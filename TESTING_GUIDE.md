# Testing Guide - Restructured Application
**Date: October 29, 2025**  
**Status: Ready for Testing**

---

## Pre-Testing Checklist

Before you begin testing, ensure:

- [ ] Web server (XAMPP) is running
- [ ] Database is accessible
- [ ] You're accessing via: `http://localhost/Scrap/`
- [ ] `.htaccess` is enabled (check Apache config for `AllowOverride All`)

---

## 1. Landing Page & Navigation

### Test: Home Page
- [ ] Visit `http://localhost/Scrap/`
- [ ] Page loads without errors
- [ ] Statistics display correctly
- [ ] All buttons and links are clickable
- [ ] Gradient backgrounds render properly

### Test: Navigation Links
- [ ] Click "Get Started Now" → Should go to `/Scrap/views/auth/signup.php`
- [ ] Click "Already a Member?" → Should go to `/Scrap/views/auth/login.php`
- [ ] Click "Find Drop-off Points" → Should go to map page
- [ ] Header logo link works
- [ ] All navigation menu items are clickable

**Expected Result:** ✅ No 404 errors, all pages load correctly

---

## 2. Authentication Flow

### Test: Signup (New User)
1. [ ] Navigate to `/Scrap/views/auth/signup.php`
2. [ ] Fill out registration form:
   - Name: Test User
   - Phone: 0712345678
   - Email: test@example.com
   - Password: Test123!
   - Location: Kiambu
3. [ ] Click "Sign Up"
4. [ ] Should redirect to OTP verification
5. [ ] Enter OTP code
6. [ ] Should redirect to citizen dashboard

**Expected Result:** ✅ Account created, logged in, redirected to `/Scrap/views/citizens/dashboard.php`

### Test: Login (Existing User)
1. [ ] Navigate to `/Scrap/views/auth/login.php`
2. [ ] Enter credentials
3. [ ] Click "Login"
4. [ ] Should redirect based on role:
   - Citizen → `/Scrap/views/citizens/dashboard.php`
   - Collector → `/Scrap/views/collectors/dashboard.php`
   - Admin → `/Scrap/views/admin/dashboard.php`

**Expected Result:** ✅ Login successful, redirected to correct dashboard

### Test: Session Management
1. [ ] Login as citizen
2. [ ] Note session is active (no login prompt)
3. [ ] Open new tab, visit `/Scrap/`
4. [ ] Should automatically redirect to dashboard
5. [ ] Click logout
6. [ ] Should redirect to `/Scrap/views/auth/login.php`
7. [ ] Visit `/Scrap/views/citizens/dashboard.php` directly
8. [ ] Should redirect to login (protected page)

**Expected Result:** ✅ Sessions persist, logout works, protected pages redirect

---

## 3. Citizen Dashboard & Features

### Test: Dashboard Access
1. [ ] Login as citizen
2. [ ] Dashboard at `/Scrap/views/citizens/dashboard.php` loads
3. [ ] Stats cards display (Recycling Score, Total Requests, Rewards, Impact)
4. [ ] Recent requests section loads
5. [ ] No console errors

**Expected Result:** ✅ Dashboard displays correctly with user data

### Test: Profile Page
1. [ ] Click profile menu → "Profile Settings"
2. [ ] Should load `/Scrap/views/citizens/profile.php`
3. [ ] User information displays
4. [ ] Can update profile fields
5. [ ] Save changes works
6. [ ] Profile photo upload (test upload functionality)

**Expected Result:** ✅ Profile loads, updates save correctly

### Test: Create Request
1. [ ] Navigate to `/Scrap/views/citizens/request.php`
2. [ ] Fill request form:
   - Material type: Plastic
   - Estimated weight: 10kg
   - Collection date: Tomorrow
   - Address: Test Address
   - Upload photo
3. [ ] Submit request
4. [ ] Should show success message
5. [ ] Request appears in history

**Expected Result:** ✅ Request created, photo uploaded to `/public/uploads/`

### Test: Request History
1. [ ] Navigate to `/Scrap/views/citizens/history.php`
2. [ ] Previous requests display
3. [ ] Click on a request → should load `/Scrap/views/citizens/request_details.php?id=X`
4. [ ] Request details display correctly
5. [ ] Status updates show
6. [ ] Can cancel pending requests

**Expected Result:** ✅ History displays, details page loads, actions work

### Test: Rewards Page
1. [ ] Navigate to `/Scrap/views/citizens/rewards.php`
2. [ ] Available rewards display
3. [ ] Current points balance shows
4. [ ] Can view reward details
5. [ ] Redeem button functions

**Expected Result:** ✅ Rewards display, redemption works

### Test: Map View
1. [ ] Navigate to `/Scrap/views/citizens/map.php`
2. [ ] Map loads with markers
3. [ ] Drop-off locations display
4. [ ] Can click markers for details
5. [ ] Geolocation prompts (if enabled)

**Expected Result:** ✅ Map renders, markers display correctly

### Test: Guide Page
1. [ ] Navigate to `/Scrap/views/citizens/guide.php`
2. [ ] Recycling guide content displays
3. [ ] Material categories show
4. [ ] Images/icons load

**Expected Result:** ✅ Guide displays educational content

---

## 4. Collector Features

### Test: Collector Registration
1. [ ] Login as citizen (without collector role)
2. [ ] Go to profile page
3. [ ] Click "Register as a collector"
4. [ ] Should navigate to `/Scrap/views/collectors/register.php`
5. [ ] Fill registration form:
   - Vehicle type: Motorcycle
   - License plate: ABC 123D
   - Upload: ID, KRA PIN, Proof of Address
6. [ ] Submit application
7. [ ] Files should upload to `/public/uploads/collectors/`

**Expected Result:** ✅ Application submitted, files uploaded correctly

### Test: Collector Dashboard
1. [ ] Login as collector (or promote test user)
2. [ ] Should redirect to `/Scrap/views/collectors/dashboard.php`
3. [ ] Available requests display
4. [ ] Can accept requests
5. [ ] Active collections show
6. [ ] Earnings summary displays

**Expected Result:** ✅ Collector dashboard functional, requests load

### Test: Accept/Complete Request
1. [ ] View available requests
2. [ ] Click "Accept" on a request
3. [ ] Request moves to "Active"
4. [ ] Navigate to request details
5. [ ] Click "Complete Collection"
6. [ ] Request status updates to "Completed"
7. [ ] Earnings update

**Expected Result:** ✅ Request workflow functions correctly

### Test: Collector Earnings
1. [ ] Navigate to `/Scrap/views/collectors/earnings.php`
2. [ ] Total earnings display
3. [ ] Completed collections list
4. [ ] Payment history shows
5. [ ] Can request payout

**Expected Result:** ✅ Earnings tracked correctly

---

## 5. Admin Features

### Test: Admin Dashboard
1. [ ] Login as admin
2. [ ] Should redirect to `/Scrap/views/admin/dashboard.php`
3. [ ] System stats display
4. [ ] Charts/graphs render
5. [ ] Quick actions work

**Expected Result:** ✅ Admin dashboard displays system overview

### Test: Manage Collectors
1. [ ] Navigate to `/Scrap/views/admin/collectors.php`
2. [ ] Collector list displays
3. [ ] Pending applications show
4. [ ] Can approve/reject collectors
5. [ ] Can view collector details
6. [ ] Can deactivate collectors

**Expected Result:** ✅ Collector management works

### Test: Manage Requests
1. [ ] Navigate to `/Scrap/views/admin/requests.php`
2. [ ] All requests display
3. [ ] Can filter by status
4. [ ] Can search requests
5. [ ] Can view request details
6. [ ] Can manually update status

**Expected Result:** ✅ Request management functional

### Test: Manage Drop-offs
1. [ ] Navigate to `/Scrap/views/admin/dropoffs.php`
2. [ ] Drop-off locations list
3. [ ] Can add new location
4. [ ] Can edit location
5. [ ] Can delete location
6. [ ] Changes reflect on map

**Expected Result:** ✅ Drop-off management works

### Test: Manage Rewards
1. [ ] Navigate to `/Scrap/views/admin/rewards.php`
2. [ ] Rewards catalog displays
3. [ ] Can create new reward
4. [ ] Upload reward image
5. [ ] Image uploads to `/public/uploads/rewards/`
6. [ ] Can edit/delete rewards

**Expected Result:** ✅ Reward management functional, images upload correctly

### Test: Reports
1. [ ] Navigate to `/Scrap/views/admin/reports.php`
2. [ ] Report options display
3. [ ] Can generate reports
4. [ ] Export functionality works
5. [ ] Date filters work

**Expected Result:** ✅ Reports generate correctly

---

## 6. File Upload Verification

### Critical: Upload Path Testing

**Check File Upload Paths:**
```bash
# After uploading files, verify they're in the correct location:
ls -la /Applications/XAMPP/xamppfiles/htdocs/Scrap/public/uploads/
ls -la /Applications/XAMPP/xamppfiles/htdocs/Scrap/public/uploads/collectors/
ls -la /Applications/XAMPP/xamppfiles/htdocs/Scrap/public/uploads/rewards/
```

**Check Database Paths:**
```sql
-- Request photos should be: public/uploads/filename.jpg
SELECT id, photo_path FROM collection_requests WHERE photo_path IS NOT NULL LIMIT 5;

-- Collector docs should be: YYYY/MM/DD/filename.ext
SELECT id, national_id_file, kra_pin_file FROM collector_applications LIMIT 5;
```

**Expected Result:** 
- ✅ Files physically exist in `/public/uploads/`
- ✅ Database paths match actual file locations
- ✅ Images display correctly in views

---

## 7. Backward Compatibility

### Test: Old URLs (via .htaccess)
- [ ] Visit `http://localhost/Scrap/login.php` → Should redirect to `/views/auth/login.php`
- [ ] Visit `http://localhost/Scrap/dashboard.php` → Should redirect to `/views/citizens/dashboard.php`
- [ ] Visit `http://localhost/Scrap/signup.php` → Should redirect to `/views/auth/signup.php`

**Expected Result:** ✅ Old URLs work via .htaccess rewrites

---

## 8. Error Handling

### Test: Error Pages
- [ ] Visit non-existent page → Should show 404
- [ ] Try accessing admin page as citizen → Should redirect
- [ ] Try accessing collector page as citizen → Should redirect
- [ ] Invalid login credentials → Show error
- [ ] Upload oversized file → Show error

**Expected Result:** ✅ Proper error messages, no crashes

---

## 9. Browser Console Checks

**Check for Errors:**
1. [ ] Open browser DevTools (F12)
2. [ ] Navigate to Console tab
3. [ ] Visit all major pages
4. [ ] Check for:
   - ❌ Red errors (fix immediately)
   - ⚠️ Yellow warnings (review)
   - ℹ️ Blue info (usually fine)

**Common Issues:**
- Missing CSS files
- Broken image paths
- JavaScript errors
- CORS issues
- Failed AJAX requests

**Expected Result:** ✅ No critical errors in console

---

## 10. Mobile Responsiveness

### Test: Mobile View
1. [ ] Open DevTools (F12)
2. [ ] Toggle device toolbar (Ctrl+Shift+M)
3. [ ] Test on:
   - [ ] iPhone SE (375px)
   - [ ] iPhone 12 Pro (390px)
   - [ ] iPad (768px)
   - [ ] Desktop (1920px)
4. [ ] Check:
   - Navigation menu works
   - Forms are usable
   - Buttons are tappable
   - Text is readable
   - Images resize properly

**Expected Result:** ✅ Responsive design works on all screen sizes

---

## 11. PWA Features (Optional)

### Test: Progressive Web App
1. [ ] Visit site on mobile browser
2. [ ] Check for "Install App" prompt
3. [ ] Install as PWA
4. [ ] Test offline functionality
5. [ ] Test push notifications (if implemented)

**Expected Result:** ✅ PWA installs and works offline

---

## Common Issues & Fixes

### Issue: 404 Page Not Found
**Cause:** `.htaccess` not enabled or wrong paths  
**Fix:** 
```apache
# Check XAMPP httpd.conf
# Ensure: AllowOverride All
# Restart Apache
```

### Issue: Images Not Loading
**Cause:** Wrong paths in database  
**Fix:** Update database paths (see ASSETS_CONSOLIDATION.md)

### Issue: Can't Login
**Cause:** Session issues  
**Fix:** 
```php
// Check config.php session settings
session_start();
// Clear browser cookies
```

### Issue: Upload Fails
**Cause:** Directory permissions  
**Fix:**
```bash
chmod 755 /Applications/XAMPP/xamppfiles/htdocs/Scrap/public/uploads/
chmod 755 /Applications/XAMPP/xamppfiles/htdocs/Scrap/public/uploads/collectors/
chmod 755 /Applications/XAMPP/xamppfiles/htdocs/Scrap/public/uploads/rewards/
```

---

## Database Migration (If Needed)

If you have existing data with old paths:

```sql
-- Backup first!
CREATE TABLE collection_requests_backup AS SELECT * FROM collection_requests;

-- Update paths
UPDATE collection_requests 
SET photo_path = REPLACE(photo_path, 'uploads/', 'public/uploads/')
WHERE photo_path LIKE 'uploads/%';

-- Verify
SELECT photo_path FROM collection_requests WHERE photo_path LIKE '%uploads%' LIMIT 5;
```

---

## Test Results Template

Use this to track your testing:

```
Date: ___________
Tester: __________

[ ] Landing Page Works
[ ] Login Works (Citizen/Collector/Admin)
[ ] Signup Works
[ ] Profile Updates
[ ] Create Request (with photo upload)
[ ] View Request History
[ ] Rewards Display
[ ] Map Loads
[ ] Collector Registration
[ ] Admin Dashboard
[ ] File Uploads Work
[ ] Old URLs Redirect
[ ] No Console Errors
[ ] Mobile Responsive

Issues Found:
1. _______________________
2. _______________________
3. _______________________

Overall Status: ✅ Pass / ❌ Fail
```

---

## Final Checklist

Before going live:

- [ ] All tests pass
- [ ] No console errors
- [ ] File uploads work
- [ ] Database paths correct
- [ ] Permissions set correctly
- [ ] .htaccess working
- [ ] Mobile responsive
- [ ] Error handling works
- [ ] Session management solid
- [ ] Backups created

---

**Ready to test!** Go through each section systematically. Document any issues you find. 🚀
