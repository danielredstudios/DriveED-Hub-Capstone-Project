<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleLesson extends Model
{
    use HasSchoolScope;
    use HasFactory;

    /**
     * Allowed tags for lesson rich-text content.
     * Keep this intentionally small to minimize XSS surface.
     */
    private const ALLOWED_CONTENT_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u',
        'ul', 'ol', 'li',
        'h1', 'h2', 'h3', 'h4',
        'blockquote', 'a',
    ];

    protected $fillable = [
        'school_id',
        'module_id',
        'title',
        'content',
        'attachments',
        'video_url',
        'sort_order',
    ];

    protected $casts = [
        'attachments' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Get the school
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the module that owns this lesson
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class , 'module_id');
    }

    /**
     * Get the course through the module
     */
    public function course(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            Course::class ,
            CourseModule::class ,
            'id', // Foreign key on course_modules table
            'id', // Foreign key on courses table
            'module_id', // Local key on module_lessons table
            'course_id' // Local key on course_modules table
        );
    }

    public function completions()
    {
        return $this->hasMany(StudentLessonCompletion::class, 'lesson_id');
    }

    /**
     * Scope to get lessons ordered by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Check if lesson has attachments
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    /**
     * Check if lesson has video
     */
    public function hasVideo(): bool
    {
        return !empty($this->video_url);
    }

    /**
     * Always return sanitized lesson HTML.
     */
    public function getContentAttribute($value): ?string
    {
        return self::sanitizeRichText($value);
    }

    /**
     * Sanitize and store lesson HTML content.
     */
    public function setContentAttribute($value): void
    {
        $this->attributes['content'] = self::sanitizeRichText($value);
    }

    /**
     * Allow safe formatting while removing unsafe tags/attributes/protocols.
     */
    private static function sanitizeRichText($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $html = trim((string) $value);
        if ($html === '') {
            return null;
        }

        if (!class_exists(\DOMDocument::class)) {
            return strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><blockquote><a>');
        }

        $previousLibxmlState = libxml_use_internal_errors(true);

        $document = new \DOMDocument('1.0', 'UTF-8');
        $wrapped = '<div>' . $html . '</div>';
        $document->loadHTML('<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $root = $document->documentElement;
        if ($root instanceof \DOMNode) {
            $nodesToSanitize = [];
            foreach ($root->childNodes as $childNode) {
                $nodesToSanitize[] = $childNode;
            }
            foreach ($nodesToSanitize as $childNode) {
                self::sanitizeDomNode($childNode);
            }
        }

        $cleanHtml = '';
        if ($root instanceof \DOMNode) {
            $children = [];
            foreach ($root->childNodes as $childNode) {
                $children[] = $childNode;
            }
            foreach ($children as $childNode) {
                $cleanHtml .= $document->saveHTML($childNode);
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previousLibxmlState);

        $cleanHtml = trim($cleanHtml);

        return $cleanHtml === '' ? null : $cleanHtml;
    }

    private static function sanitizeDomNode(\DOMNode $node): void
    {
        $children = [];
        foreach ($node->childNodes as $childNode) {
            $children[] = $childNode;
        }

        foreach ($children as $childNode) {
            self::sanitizeDomNode($childNode);
        }

        if ($node->nodeType === XML_COMMENT_NODE) {
            if ($node->parentNode instanceof \DOMNode) {
                $node->parentNode->removeChild($node);
            }
            return;
        }

        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return;
        }

        $tagName = strtolower($node->nodeName);

        if (!in_array($tagName, self::ALLOWED_CONTENT_TAGS, true)) {
            self::unwrapNode($node);
            return;
        }

        self::sanitizeAttributes($node, $tagName);
    }

    private static function sanitizeAttributes(\DOMNode $node, string $tagName): void
    {
        if (!($node instanceof \DOMElement) || !$node->hasAttributes()) {
            return;
        }

        $allowedAttributes = $tagName === 'a' ? ['href', 'target', 'rel'] : [];
        $attributesToRemove = [];

        foreach ($node->attributes as $attribute) {
            $attributeName = strtolower($attribute->nodeName);

            if (str_starts_with($attributeName, 'on')) {
                $attributesToRemove[] = $attributeName;
                continue;
            }

            if (!in_array($attributeName, $allowedAttributes, true)) {
                $attributesToRemove[] = $attributeName;
            }
        }

        foreach ($attributesToRemove as $attributeName) {
            $node->removeAttribute($attributeName);
        }

        if ($tagName !== 'a') {
            return;
        }

        $href = trim((string) $node->getAttribute('href'));
        if ($href === '') {
            $node->removeAttribute('href');
            $node->removeAttribute('target');
            $node->removeAttribute('rel');
            return;
        }

        $normalizedHref = strtolower($href);
        $isSafeHref = str_starts_with($normalizedHref, 'http://')
            || str_starts_with($normalizedHref, 'https://')
            || str_starts_with($normalizedHref, 'mailto:')
            || str_starts_with($normalizedHref, 'tel:')
            || str_starts_with($normalizedHref, '/')
            || str_starts_with($normalizedHref, '#');

        if (!$isSafeHref) {
            $node->removeAttribute('href');
            $node->removeAttribute('target');
            $node->removeAttribute('rel');
            return;
        }

        $target = strtolower(trim((string) $node->getAttribute('target')));
        if ($target !== '_blank') {
            $node->removeAttribute('target');
            $node->removeAttribute('rel');
            return;
        }

        $node->setAttribute('rel', 'noopener noreferrer');
    }

    private static function unwrapNode(\DOMNode $node): void
    {
        $parent = $node->parentNode;
        if (!($parent instanceof \DOMNode)) {
            return;
        }

        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }
}
