# Phase Progression Page: Implementation Context

## Purpose
The Phase Progression Page is responsible for managing and displaying the workflow that governs a student's advancement through the required phases of their enrollment (typically: theoretical → practical → completed). It enables authorized users (such as admins) to review, approve, or reject requests for phase transitions, ensuring that all business rules and prerequisites are enforced before a student can progress.

## Phase-Based Progression Workflow
- Each student enrollment is associated with a current phase (e.g., 'theoretical', 'practical').
- Progression from one phase to the next is not automatic; it requires an explicit request (phase progression request) and subsequent approval by an authorized admin.
- The system tracks these requests in the `phase_progression_requests` table, recording the source phase, target phase, request status, timestamps, reviewer, and any admin notes.

## Conditions for Progression
- A student may only request progression to the next phase if all requirements for the current phase are satisfied (e.g., completion of all theoretical hours before moving to practical).
- Only one pending progression request per enrollment and phase transition is allowed at a time.
- An admin must review each request and either approve or reject it. Approval moves the enrollment to the next phase; rejection keeps the student in the current phase.
- The system prevents duplicate or out-of-order phase transitions.

## Completion of All Phases
- When a student completes the final phase (e.g., practical), and the corresponding progression request is approved, their enrollment is marked as 'completed.'
- No further phase progression requests are permitted once the enrollment is completed.

## Relation to Requisition Approval and Job Posting Eligibility
- Phase progression is a prerequisite for certain system actions, such as requisition approval or eligibility for job postings.
- Only students who have completed all required phases (i.e., whose enrollment status is 'completed') are eligible for requisition approval and can be considered for job postings.
- The system enforces these dependencies by checking the enrollment's phase status before allowing related actions.

## Intended System Behavior
- Enforce strict, sequential phase progression with admin oversight.
- Ensure all prerequisites are met before allowing a phase transition.
- Prevent duplicate or invalid progression requests.
- Update enrollment status and eligibility for downstream actions (like requisition approval and job posting) based on phase completion.
- Maintain a clear audit trail of all progression requests, decisions, and notes for accountability and traceability.

This document provides the workflow logic and progression rules for the Phase Progression Page, serving as a reference for developers and AI agents implementing or modifying this feature.