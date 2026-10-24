# DriveED Hub — Capstone Defense Master Guide & Presentation Playbook
**Version:** 3.0 (Auditorium & Panel Defense Edition)  
**Last Updated:** October 24, 2026  
**Audience:** Capstone Project Team Members & Presenters  

---

## 1. Attendance, Arrival Time & Team Readiness (Item 10)

### 1.1 Arrival Buffer & Timeline
- **Target Arrival Time:** **45 to 60 minutes prior** to the scheduled defense slot.
- **Why early arrival is critical:**
  - Auditorium/Room AV setup: testing projector aspect ratio (16:9), HDMI handshake, audio output for demo video, and dual-laptop screen mirroring.
  - Verification of Docker containers and local network stability before panel entry.
  - Zero-stress buffer: eliminates rush, allows team mental briefing, and avoids panel reprimands regarding punctuality.

### 1.2 Team Roster & Role Assignment
All members must be physically present and in professional attire. If one member is missing, panel rules may cause immediate forfeiture or point deductions.

| Member | Primary Presentation Role | Demonstration Station | Primary Talking Points |
|---|---|---|---|
| **Member 1 (Team Lead)** | Opening, Problem Statement, System Scope & LTO Boundary | Podium / Slide Controller | Project background, LTO statutory boundary, architecture overview |
| **Member 2 (Front-Facing Demo)** | Guest & Student Scenario Walkthrough | **Laptop A** (Student/Guest) | Registration, OTP verification, course enrollment, schedule booking, TDC modules |
| **Member 3 (Back-Office Demo)** | Instructor & Admin Scenario Walkthrough | **Laptop B** (Admin/Instructor) | Payment verification, session logging, timeslot removal, user license management |
| **Member 4 (Technical & Q&A Lead)** | Architecture, Database, Defense Q&A | Q&A Standing Station | Multi-tenancy isolation, audit logs, expiration reminder logic, security |

---

## 2. Presentation Equipment & Setup Strategy (Item 6)

### 2.1 The Two-Laptop Dual Live-Sync Architecture
To avoid the friction of constantly logging in and out of different roles during live panel evaluation, two laptops operate simultaneously in real time.

```
+-----------------------------------------------------------+
|                      AUDITORIUM DISPLAY                   |
|           (Projector / Large TV via HDMI Switch)          |
+-----------------------------------------------------------+
                             ^
                             |
         +-------------------+-------------------+
         |                                       |
+-----------------------+               +-----------------------+
|       LAPTOP A        |               |       LAPTOP B        |
|  (Learner Facing)     |               | (Back-Office Ops)     |
|                       |               |                       |
| • Guest Portal        |               | • School Admin Portal |
| • Student Dashboard   |    Network    | • Instructor Portal   |
| • Schedule Calendar   | <===========> | • Payment Ledger      |
| • Module Assessments  | (Docker Host) | • Session Logging     |
+-----------------------+               +-----------------------+
```

### 2.2 Laptop Station Checklist
- **Laptop A (Learner Station):**
  - Displays: `http://localhost:8004/drived-hub` (or connected via LAN IP of Laptop B).
  - Open Browser Tabs:
    1. Slide Presentation Deck (PowerPoint / Canva).
    2. Guest Landing Page (`/{school}`).
    3. Student Dashboard (`/{school}/student`).
    4. Student License & Profile (`/{school}/student/profile`).
- **Laptop B (Operations Station / Host):**
  - Runs: Docker container `driveed_hub_app` and `driveed_hub_db`.
  - Open Browser Tabs:
    1. School Admin Dashboard (`/{school}/admin`).
    2. Admin Payment Ledger (`/{school}/admin/payments`).
    3. Admin User Management with License Expiration Badges (`/{school}/admin/user-management`).
    4. Instructor Dashboard & Session Creator (`/{school}/instructor/sessions/create`).
- **Hardware & Peripherals Checklist:**
  - [ ] 2x Laptop chargers (plugged into AC power, do not rely on battery).
  - [ ] 1x HDMI cable + USB-C to HDMI adapters (for auditorium projector).
  - [ ] 1x Clicker / wireless presentation remote.
  - [ ] 1x Backup Flash Drive (USB 3.0) containing presentation slides, demo video, and code repo.
  - [ ] 1x Standard CD-R disc containing the complete Demo Video with Google Voice-Over (as requested for submission).

---

## 3. Defense Presentation Script & Delivery Flow (Item 3)

### Phase 1: Opening & Legal Scope Clarification (3 Minutes)
- **Speaker:** Member 1
- **Script:**
  > *"Good morning, esteemed members of the panel, panel chair, and faculty advisers. We are proud to present **DriveED Hub**, a specialized multi-tenant driving school management and curriculum tracking platform.  
  > Before demonstrating our system workflows, we want to establish a fundamental statutory scope boundary:  
  > **The actual issuance, physical printing, and legal renewal of driver's licenses are conducted exclusively by the Land Transportation Office (LTO).**  
  > DriveED Hub does not issue driver's licenses. Instead, our platform modernizes the driving school's accredited educational ecosystem—handling Theoretical Driving Courses (TDC), Practical Driving Courses (PDC), student enrollment, real-time vehicle schedule booking, instructor competency evaluation, and proactive license expiration reminders."*

### Phase 2: Live Demonstration of Scenarios (8-10 Minutes)
- **Speakers:** Member 2 (Laptop A) & Member 3 (Laptop B)
- **Cues & Synchronization:**

#### Demonstration 1: Smooth Enrollment & Cross-Role Approval
1. **Presenter on Laptop A (Student):**
   - Shows guest account registration with Data Privacy Notice and OTP email verification.
   - Selects "TDC + PDC Combined Course" and submits GCash payment reference: `REF-88992211`.
2. **Presenter on Laptop B (Admin):**
   - Refreshes Admin Payments: shows `REF-88992211` instantly appear.
   - Clicks **Mark as Paid** and approves enrollment.
3. **Presenter on Laptop A (Student):**
   - Refreshes page: learner role immediately promotes from `Guest` to `Student`.
   - Opens Schedule, chooses Instructor Carlos and light vehicle for Tuesday 10:00 AM, confirms slot.
4. **Presenter on Laptop B (Instructor):**
   - Instructor opens Schedule: slot is locked in real-time. Instructor opens `Session Logging`, inputs 2.0 hours completed, rates road skills 5/5, and submits.
5. **Presenter on Laptop A (Student):**
   - Student dashboard updates to reflect progress and displays the downloadable certified Session Completion Report PDF.

#### Demonstration 2: Exception Handling & Schedule Conflict
1. **Presenter on Laptop A:** Shows Module 3 quiz failure (65%) triggering a review requirement; shows retake with 88% unlocking the practical phase.
2. **Presenter on Laptop B:** Simulates emergency fleet repair. Instructor requests slot removal; Admin approves in `Removal Requests`.
3. **Presenter on Laptop A:** Shows student receiving notification with zero penalty and restored booking credit.

#### Demonstration 3: Expired License & Expiration Reminder Feature
1. **Presenter on Laptop A:**
   - Navigates to Student Profile. Inputs Driver's License `N01-22-123456` with expiration date set to 20 days from today.
   - Shows live dashboard displaying the prominent **Amber Warning Banner**: *"Driver's License Approaching Expiration (20 days remaining)"* alongside the direct advisory to renew through the LTO LTMS portal.
2. **Presenter on Laptop B (Admin):**
   - Opens User Management: shows the real-time `Expiring in 20 days` badge in the student table.
   - Demonstrates the automated notification dispatched to the user's notification bell.
3. **Presenter on Laptop A:**
   - Demonstrates the scenario of a license expired for over 2 years: shows the **Red Critical Banner** advising the student that under LTO regulations, re-examination is required and guiding them into refresher practical sessions.

---

## 4. Auditorium Demo Video & Google Voice-Over Storyboard (Item 4)

Use this precise storyboard and narration script to record the video and burn to the presentation CD.

| Timecode | Visual Screen Action | Voice-Over Narration (Google TTS) |
|---|---|---|
| **00:00 - 00:25** | High-resolution title card, landing page `/{school}`, branded color theme. | *"Welcome to DriveED Hub, an all-in-one management platform for accredited driving schools. The platform provides structured theoretical and practical driver education while maintaining strict alignment with Land Transportation Office standards."* |
| **00:25 - 00:55** | Guest registers, accepts Data Privacy policy, inputs OTP on email verification screen. | *"Prospective learners register through their driving school's dedicated portal. Automated email one-time password verification secures the learner's identity before course enrollment begins."* |
| **00:55 - 01:30** | Course catalog, selecting TDC + PDC package, submitting GCash payment proof. | *"Learners select accredited course packages and submit electronic payment proof via GCash or cash. School administrators verify payment ledgers in real time, elevating verified guests to active students."* |
| **01:30 - 02:10** | Student modular learning, quiz assessment, instructor theoretical signoff. | *"The Theoretical Driving Course offers modular curriculum content with chapter assessments. Instructors review quiz attempts and confirm theoretical eligibility before road scheduling unlocks."* |
| **02:10 - 02:50** | Calendar timeslot selection, instructor road session logging, competency grading. | *"Practical driving course sessions are scheduled via an interactive visual calendar, preventing double-booking across vehicles. Instructors log consumed hours, road competencies, and notes directly into the session ledger."* |
| **02:50 - 03:30** | License expiration reminder banner on dashboard, LTO LTMS guide button, admin badge. | *"To assist drivers with timely compliance, DriveED Hub features automated driver's license expiration reminders. As expiration dates approach, users receive targeted alerts directing them to the official LTO LTMS portal for statutory renewal."* |
| **03:30 - 04:00** | Student downloading PDF completion report; summary of system security and multi-tenancy. | *"DriveED Hub: Bridging driving school excellence and regulatory road safety. Thank you."* |

---

## 5. Defense Day Emergency Troubleshooting Contingency Plan

| Potential Issue | Immediate Action During Defense |
|---|---|
| Projector resolution distorts UI | Press `Ctrl + 0` in Chrome/Edge to reset zoom to 100%, or `F11` for clean full-screen presentation mode. |
| Auditorium WiFi drops or is slow | Laptop B runs everything locally on `localhost:8004` (Docker). Laptop A connects directly to Laptop B's mobile hotspot. Zero internet dependency required for core demo. |
| CD player in auditorium cannot read disc | Backup USB flash drive contains the MP4 video file and PowerPoint slides directly accessible via plug-and-play. |
| Panel asks: *"Can a student renew their license through your app?"* | **Immediate Clear Response:** *"No, sir/ma'am. Under Philippine law, only the LTO has the legal authority to renew or issue driver's licenses. DriveED Hub handles driver education, practical training records, and expiration reminder alerts, guiding learners directly to the LTO LTMS portal."* |
