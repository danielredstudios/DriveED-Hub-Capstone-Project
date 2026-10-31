# Local TDC migration verification

The repair migration was applied to the running local Docker application on 2026-10-31.
It reported zero eligible enrollments and zero locks repaired. No approved standalone
TDC enrollment in this database had its enrollment-level theoretical passing flag set.

SHA-256 comparisons before and after migration and browser inspection confirmed:

- 17 session completion records unchanged.
- 62 bookings unchanged.
- 4 progress records unchanged.
- 2 phase progression records unchanged.
- 9 Combo enrollments and their 9 students' lock references unchanged.
- The legacy enrollments, lesson completions, and admin logs tables were empty and unchanged.

Luna Marie Martinez (student 10) had a passed TDC flag on her student profile and a
verified license, while her original TDC enrollment 10 was still approved and its
theoretical passing flag was false. After explicit user confirmation, enrollment 10
was marked passed using her existing profile passing date and completed through the
repair command. Her two session records were preserved. She was already unlocked;
no lock change was needed for this local student.

Her PDC application was submitted through the actual local student enrollment form:
course 2, PDC 10-Hour Manual, at her existing branch. The server created separate
enrollment request 74 with pending status. No payment receipt was submitted and no
admin approval was performed.

`pdc-application-filled.png` shows the completed application form.
`pdc-submitted.png` shows the payment prompt returned by successful submission.
`pdc-enrollment-confirmed.png` shows Luna's PDC course page with Payment Needed status.

After submission, all training and Combo row counts and SHA-256 hashes still matched
the original pre-migration baseline.

`verification.json` contains the measured row counts and hashes. Recheck with:

```sh
docker exec driveed_hub_app php tests/verify-local-tdc.php enrolled
```
