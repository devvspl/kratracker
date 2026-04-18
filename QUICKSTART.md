# Quick Start Guide - KRA Tracker

## Get Started in 3 Minutes

### 1. Start the Application
```bash
cd kra-tracker
php artisan serve
```

### 2. Open Your Browser
Navigate to: **http://localhost:8000**

### 3. Login with Test Credentials

**Admin Account** (Full Access)
- Email: `admin@example.com`
- Password: `password`

**Manager Account** (View team logs, add feedback)
- Email: `manager@example.com`
- Password: `password`

**Employee Account** (Manage own logs)
- Email: `employee@example.com`
- Password: `password`

## First Steps After Login

### As an Employee

1. **View Dashboard**
   - See your KRA performance metrics
   - View charts showing your progress

2. **Add Your First Work Log**
   - Click "Work Logs" in sidebar
   - Click "+ Add Work Log" button
   - Fill in the form:
     - Select Sub-KRA (e.g., "Change Request")
     - Enter title (e.g., "Fixed login bug")
     - Set achievement value (e.g., 5)
     - Choose priority and status
   - Click "Save"

3. **View Your Score**
   - Score is automatically calculated based on Logic type
   - See weighted contribution to overall KRA

4. **Export Your Data**
   - Go to Dashboard
   - Click "Export Work Logs (Excel)" or "Export Analytics (PDF)"

### As an Admin

1. **Manage Master Data**
   - Click "Masters" section in sidebar
   - Add/Edit KRAs, Applications, Priorities, etc.
   - All changes are immediately reflected

2. **Add New Applications**
   - Go to Masters > Applications
   - Click "+ Add Application"
   - Enter name, tech stack, description
   - Employees can now link work logs to this app

3. **Configure Period Targets**
   - Set monthly/quarterly/annual targets per Sub-KRA
   - Targets are used for automatic score calculation

### As a Manager

1. **View Team Performance**
   - Access all team members' work logs
   - View analytics across the team

2. **Add Manager Feedback**
   - Open any work log
   - Add feedback with rating (1-5 stars)
   - Employee receives notification

## Key Features to Try

### 1. Filters
- On Work Logs page, use filters to find specific entries
- Filter by date range, KRA, status, or priority

### 2. Inline Status Update
- Quickly change task status from the list view
- No need to open the full edit modal

### 3. Score Calculation
- **Logic 1 (Proportional)**: Score = (Achievement/Target) × 100
- **Logic 3 (Binary)**: Score = 100 if Achievement >= Target, else 0
- Weighted score = Score × Sub-KRA weightage

### 4. Charts & Analytics
- Dashboard shows real-time performance metrics
- KRA-wise bar chart
- Daily trend line chart (last 30 days)

### 5. Export Options
- **Excel**: Detailed work logs with all fields
- **Excel**: KRA summary with scores and weightages
- **PDF**: Analytics report with charts as tables

## Sample Workflow

### Daily Work Logging
1. Login as employee
2. Go to Work Logs
3. Click "+ Add Work Log"
4. Fill details:
   - Sub-KRA: "User Queries & Team Support"
   - Title: "Resolved 10 user queries"
   - Achievement: 10
   - Status: Completed
5. Save and see automatic score calculation

### Weekly Review
1. Login as manager
2. View team's work logs
3. Add feedback to completed tasks
4. Export weekly summary

### Monthly Reporting
1. Login as admin/employee
2. Go to Dashboard
3. Review KRA performance metrics
4. Export analytics PDF for records

## Tips & Tricks

- **Keyboard Shortcuts**: Press ESC to close modals
- **Quick Add**: Date field defaults to today
- **Bulk Export**: Use filters before exporting to get specific data
- **Score Tracking**: Check dashboard daily to monitor progress
- **Feedback Loop**: Add self-feedback to track personal notes

## Common Tasks

### Change Your Password
1. Click on your avatar (top right)
2. Go to Profile
3. Update password

### Add a New KRA (Admin Only)
1. Go to Masters > KRAs
2. Click "+ Add KRA"
3. Enter name, weightage, description
4. Add Sub-KRAs under it

### Set Period Targets (Admin Only)
1. Go to Masters > Period Targets
2. Select Sub-KRA
3. Set target value for the period
4. Choose period type (Monthly/Quarterly/Annually)

## Need Help?

- Check the full README.md for detailed documentation
- All features use intuitive UI with clear labels
- Toast notifications confirm all actions
- Error messages guide you if something goes wrong

## What's Next?

- Explore all master data configurations
- Set up email notifications (update .env)
- Customize the theme colors (tailwind.config.js)
- Add more users and assign roles
- Create custom reports using export features

---

**Enjoy tracking your KRAs! 🎯**
