# Role Permissions: School Admin vs Branch Secretary

This document outlines the differences between **School Administrators** and **Branch Secretaries** (Branch Managers) in the Driving School Management System.

---

## 🎯 Quick Overview

| Role | Scope | Purpose |
|------|-------|---------|
| **School Admin** | Entire school (all branches) | Full administrative control over the school |
| **Branch Secretary** | Single assigned branch only | Day-to-day operations management at branch level |

---

## ✅ What Branch Secretaries CAN Do

### User Management
- ✅ View students and instructors **in their branch**
- ✅ Create new student accounts (for their branch)
- ✅ Create new instructor accounts (for their branch)
- ✅ Update student/instructor information
- ✅ Toggle student/instructor status (active/inactive)

### Enrollment Management
- ✅ View enrollment requests for their branch
- ✅ Approve/Reject enrollment requests
- ✅ Confirm payment status on enrollments
- ✅ Mark theoretical training as passed
- ✅ Verify student licenses

### Scheduling & Sessions
- ✅ Create and manage schedules for their branch
- ✅ View and manage student bookings
- ✅ View session completions logged by instructors
- ✅ Approve/Reject phase progression requests

### Payments
- ✅ View payment records for their branch
- ✅ Confirm payments

### Course Management
- ✅ View courses
- ✅ Manage course packages

### Student Action Requests
- ✅ **Create** requests to add or remove students
- ❌ **Cannot approve/deny** these requests (requires School Admin)

---

## ❌ What Branch Secretaries CANNOT Do

### Administrative Functions
- ❌ Access data from **other branches**
- ❌ Manage other admins or secretaries
- ❌ Create/Edit/Delete admin accounts
- ❌ View Reports & Analytics dashboard
- ❌ Manage school-wide settings
- ❌ Create or manage branches
- ❌ Approve Student Action Requests (add/remove students)

### System-Wide Access
- ❌ See school-wide statistics
- ❌ View all branches' data
- ❌ Generate school-wide reports

---

## 🔐 School Admin Exclusive Features

| Feature | Description |
|---------|-------------|
| **Admin Management** | Create, edit, and manage all admin and secretary accounts |
| **Branch Management** | Create new branches, edit branch details, toggle branch status |
| **School Settings** | Configure school-wide settings, branding, and preferences |
| **Reports & Analytics** | Access comprehensive reports and analytics across all branches |
| **Approve Student Requests** | Final approval/denial of student add/remove requests from secretaries |
| **Cross-Branch Access** | View and manage data from any branch |

---

## 📊 Navigation Menu Comparison

### Both Roles See:
- 📌 Dashboard
- 👥 User Management (Students, Instructors)
- 📝 Enrollment Management
- 📅 Sessions (Schedules, Bookings, Session Completions, Phase Progressions)
- 💳 Payments
- 📚 Course Management
- 🔔 Student Action Requests
- 👤 Profile

### School Admin Only:
- 📊 **Reports & Analytics**
- ⚙️ **Internal Management**
  - Admin Management
  - Branches
  - Settings

---

## 🔄 Student Action Request Workflow

This is a key workflow that demonstrates the collaboration between roles:

```
┌─────────────────────┐     ┌──────────────────────┐     ┌─────────────────────┐
│  Branch Secretary   │────▶│   Request Created    │────▶│    School Admin     │
│  Creates Request    │     │   (Pending Status)   │     │  Approves/Denies    │
└─────────────────────┘     └──────────────────────┘     └─────────────────────┘
                                                                    │
                                                                    ▼
                                                          ┌─────────────────────┐
                                                          │   Action Executed   │
                                                          │ (Student Added/     │
                                                          │  Removed)           │
                                                          └─────────────────────┘
```

**Why this workflow exists:**
- Branch Secretaries handle day-to-day operations and identify students needing changes
- School Admins maintain oversight and approve significant changes
- Creates accountability and audit trail for student management

---

## 📝 Summary Table

| Capability | School Admin | Branch Secretary |
|------------|:------------:|:----------------:|
| View own branch data | ✅ | ✅ |
| View all branches data | ✅ | ❌ |
| Manage students/instructors | ✅ All | ✅ Own branch |
| Manage enrollments | ✅ All | ✅ Own branch |
| Manage schedules | ✅ All | ✅ Own branch |
| View payments | ✅ All | ✅ Own branch |
| Confirm payments | ✅ | ✅ |
| Create student action requests | ❌ | ✅ |
| Approve student action requests | ✅ | ❌ |
| Manage admin accounts | ✅ | ❌ |
| Manage branches | ✅ | ❌ |
| School settings | ✅ | ❌ |
| Reports & Analytics | ✅ | ❌ |

---

## 🔑 Key Takeaways

1. **Branch Secretaries are branch-focused** - They can only see and manage data within their assigned branch.

2. **School Admins have full control** - They can access everything across all branches and manage system-wide settings.

3. **Approval workflows exist** - Some actions (like student add/remove) require School Admin approval even when initiated by a Branch Secretary.

4. **Separation of duties** - This structure ensures proper oversight while allowing efficient day-to-day operations at the branch level.

---

*Last Updated: March 2026*
