# Before and after review

Before images are from the supplied Word report. After images are from the local app. Click a comparison to inspect it. The role-header finding had no before screenshot in the report.

## 1. Pending email spacing and collapse

After: a temporary example invitation demonstrates recipient spacing. It was removed without sending email.

![Before and after Pending email spacing and collapse](01-comparison.png)

## 2. Collapsible schedule panels

Today’s Lessons, Upcoming This Week, and Cancelled Schedules can each be expanded.

![Before and after Collapsible schedule panels](02-comparison.png)

## 3. Instructor calendar modal closes correctly

Open and closed states. Browser verification confirms Close sends no network request.

![Before and after Instructor calendar modal closes correctly](03-comparison.png)

## 4. Student calendar modal closes correctly

Open and closed states. Browser verification confirms Close sends no network request.

![Before and after Student calendar modal closes correctly](04-comparison.png)

## 5. Calendar fits the page

After: desktop and 390px mobile layouts.

![Before and after Calendar fits the page](05-comparison.png)

## 6. Instructor calendar modal styling

After: a bounded dialog with backdrop, border, spacing, readable headings, and scrolling.

![Before and after Instructor calendar modal styling](06-comparison.png)

## 7. Collapsible schedule exports

After: collapsed and expanded export controls.

![Before and after Collapsible schedule exports](07-comparison.png)

## 8. Separate role header colors

The report states this finding in text but supplies no before screenshot. The new settings provide independent background and text colors for each role.

![Before and after Separate role header colors](08-comparison.png)

## 9. Past schedules are distinct

After: Show Past Schedules and Collapse All are enabled. Past groups carry a label and a different header color.

![Before and after Past schedules are distinct](09-comparison.png)

## 10. Student training PDF export

After: Export PDF is visible in the training hub. Both the individual record and training-list PDF endpoints were verified.

![Before and after Student training PDF export](10-comparison.png)

## 11. Enrollment verification layout

After: desktop and mobile. The local student’s missing placeholder license file is explicitly identified as unavailable.

![Before and after Enrollment verification layout](11-comparison.png)

## 12. Enrollment search

After: searching for Luna filters the list while retaining status, branch, and sort controls.

![Before and after Enrollment search](12-comparison.png)

## 13. Dashboard cards open filtered user lists

After: dashboard and the student roster reached by clicking Total Students. Other card destinations were also verified.

![Before and after Dashboard cards open filtered user lists](13-comparison.png)

## 14. Prevent sessions beyond required hours

After: a temporary full-hours test fixture shows the explanation and disabled button. A tampered request was rejected with HTTP 422. Test sessions were removed.

![Before and after Prevent sessions beyond required hours](14-comparison.png)
