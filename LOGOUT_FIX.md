# Logout 405 Error Fix

## Date: October 29, 2025

## Problem
The application was returning a **405 Method Not Allowed** error when users tried to logout:
```
GET http://localhost/Scrap/api/logout.php 405 (Method Not Allowed)
```

## Root Cause
The `/api/logout.php` endpoint only accepts **POST** requests for security reasons, but several files were making **GET** requests or missing the POST method specification:

1. **Direct href link** in `admin_sidebar.php` - triggered GET request
2. **Missing method: 'POST'** in multiple JavaScript fetch calls

## Files Fixed

### 1. `/includes/admin_sidebar.php`
**Before:**
```html
<a href="/Scrap/api/logout.php" class="...">Logout</a>
```

**After:**
```html
<button onclick="handleLogout()" class="...">Logout</button>
<script>
async function handleLogout() {
    const response = await fetch('/Scrap/api/logout.php', {
        method: 'POST',
        credentials: 'include'
    });
    if (response.ok) {
        window.location.href = '/Scrap/views/auth/login.php';
    }
}
</script>
```

### 2. `/views/admin/` pages (5 files)
**Fixed Files:**
- `dashboard.php`
- `collectors.php`
- `dropoffs.php`
- `reports.php`
- `requests.php`
- `rewards.php`

**Before:**
```javascript
fetch('/Scrap/api/logout.php', { credentials: 'include' })
```

**After:**
```javascript
fetch('/Scrap/api/logout.php', { 
    method: 'POST',
    credentials: 'include' 
})
```

### 3. `/views/collectors/profile.php`
**Before:**
```javascript
await fetch('/Scrap/api/logout.php', { credentials: 'include' });
```

**After:**
```javascript
await fetch('/Scrap/api/logout.php', { 
    method: 'POST',
    credentials: 'include' 
});
```

### 4. `/tracking.php`
**Before:**
```javascript
await fetch('/Scrap/api/logout.php');
```

**After:**
```javascript
await fetch('/Scrap/api/logout.php', {
    method: 'POST',
    credentials: 'include'
});
```

## Summary

**Total Files Fixed:** 10 files
- 1 sidebar file (changed from href to button with POST fetch)
- 6 admin view files (added method: 'POST')
- 1 collector view file (added method: 'POST')
- 1 tracking file (added method: 'POST')
- 1 collector sidebar (already had POST - no change needed)
- 1 header (already had POST - no change needed)
- 1 citizens dashboard (already had POST - no change needed)
- 1 citizens history (already had POST - no change needed)

## Result
✅ Logout now works correctly across all user roles (Admin, Collector, Citizen)
✅ No more 405 Method Not Allowed errors
✅ All logout requests use proper POST method
✅ Proper redirect to login page after logout

## Security Note
The logout endpoint requires POST requests to prevent CSRF attacks. This is a security best practice as GET requests can be triggered accidentally through:
- Browser prefetch
- Link crawlers
- Browser history/autocomplete
- Malicious links

POST requests require explicit user action and cannot be triggered through simple URL access.
