# Project Restructuring Plan
**Kiambu Recycling & Scraps Platform**  
*Date: October 29, 2025*

---

## 🔍 Current Issues Identified

### 1. **Mixed File Organization**
- PHP pages scattered in root directory (dashboard.php, profile.php, history.php, etc.)
- Inconsistent use of `/public/` folder
- Hard to distinguish between user pages, admin pages, and collector pages

### 2. **Inconsistent Public Folder Usage**
- `/public/admin/` contains admin pages (✅ Good)
- `/public/collectors/` contains collector pages (✅ Good)
- But citizen/user pages are in root (❌ Inconsistent)
- Some have `.html` extensions, some `.php`

### 3. **Path Reference Complexity**
- Absolute paths using `/Scrap/` prefix
- Relative paths in includes
- Inconsistent API path references

### 4. **Assets Scattered**
- JavaScript in `/public/js/`
- Images in `/public/images/`
- Uploads in `/uploads/` (root level)
- No centralized CSS folder

---

## 🎯 Proposed Structure

```
/Scrap/
│
├── index.php                    # Landing page (stays in root)
├── config.php                   # Configuration (stays in root)
├── composer.json                # Dependencies
├── .gitignore
├── .htaccess                    # URL rewriting rules
│
├── /api/                        # ✅ KEEP AS IS - Well organized
│   ├── /admin/                  # Admin API endpoints
│   ├── /collectors/             # Collector API endpoints
│   ├── login.php
│   ├── logout.php
│   ├── register.php
│   └── ...
│
├── /controllers/                # ✅ KEEP AS IS
│   └── AuthController.php
│
├── /models/                     # ✅ KEEP AS IS
│   ├── User.php
│   ├── Collector.php
│   ├── Request.php
│   ├── Reward.php
│   └── Dropoff.php
│
├── /includes/                   # ✅ KEEP AS IS - Improved
│   ├── auth.php
│   ├── header.php
│   ├── footer.php
│   ├── admin_sidebar.php        # ✅ New - Reusable
│   └── collector_sidebar.php
│
├── /views/                      # 🆕 NEW - Organized by user type
│   │
│   ├── /auth/                   # Authentication pages
│   │   ├── login.php            # FROM: /login.php
│   │   └── signup.php           # FROM: /signup.php
│   │
│   ├── /citizens/               # Regular user pages
│   │   ├── dashboard.php        # FROM: /dashboard.php
│   │   ├── profile.php          # FROM: /profile.php
│   │   ├── history.php          # FROM: /history.php
│   │   ├── request.php          # FROM: /request.php
│   │   ├── request_details.php  # FROM: /request_details.php
│   │   ├── rewards.php          # FROM: /rewards.php
│   │   ├── map.php              # FROM: /map.php
│   │   └── guide.php            # FROM: /guide.php
│   │
│   ├── /admin/                  # Admin pages
│   │   ├── dashboard.php        # FROM: /public/admin/dashboard.php
│   │   ├── collectors.php       # FROM: /public/admin/collectors.php
│   │   ├── requests.php         # FROM: /public/admin/requests.php
│   │   ├── dropoffs.php         # FROM: /public/admin/dropoffs.php
│   │   ├── rewards.php          # FROM: /public/admin/rewards.php
│   │   └── reports.php          # FROM: /public/admin/reports.php
│   │
│   └── /collectors/             # Collector pages
│       ├── dashboard.php        # FROM: /public/collectors/dashboard.php
│       ├── profile.php          # FROM: /public/collectors/profile.php (convert to .php)
│       ├── earnings.php         # FROM: /public/collectors/earnings.php
│       ├── requests.php         # FROM: /public/collectors/requests.php
│       └── register.php         # FROM: /public/collectors/register.php
│
├── /public/                     # 🔄 REORGANIZED - Public assets only
│   │
│   ├── /css/                    # 🆕 NEW
│   │   ├── main.css
│   │   ├── admin.css
│   │   └── collector.css
│   │
│   ├── /js/                     # ✅ KEEP (cleaned up)
│   │   ├── collector-dashboard.js
│   │   ├── collector-requests.js
│   │   ├── collector-tracker.js
│   │   └── realtime.js
│   │
│   ├── /images/                 # ✅ KEEP
│   │   └── /markers/
│   │
│   ├── /uploads/                # 🔄 MOVED FROM /uploads/
│   │   ├── /collectors/
│   │   └── /rewards/
│   │
│   ├── manifest.json            # ✅ KEEP
│   └── service-worker.js        # ✅ KEEP
│
├── /sql/                        # ✅ KEEP AS IS
│   ├── schema.sql
│   ├── collector_tables.sql
│   └── notifications_table.sql
│
├── /server/                     # ✅ KEEP AS IS
│   └── websocket.php
│
└── /mpesa/                      # ✅ KEEP AS IS
    └── mpesa_init.php
```

---

## 📋 Migration Steps

### Phase 1: Create New Structure (No Breaking Changes)
1. ✅ Create `/views/` directory with subdirectories
2. ✅ Create `/public/css/` directory
3. ✅ Move `/uploads/` to `/public/uploads/`

### Phase 2: Move Files
1. Move authentication pages to `/views/auth/`
2. Move citizen pages to `/views/citizens/`
3. Move admin pages from `/public/admin/` to `/views/admin/`
4. Move collector pages from `/public/collectors/` to `/views/collectors/`
5. Convert remaining `.html` files to `.php`

### Phase 3: Update References
1. Update all `require_once` paths in moved files
2. Update all API endpoint URLs
3. Update all redirect URLs
4. Update navigation links in headers/sidebars
5. Update `.htaccess` for cleaner URLs

### Phase 4: Testing
1. Test authentication flow
2. Test all citizen pages
3. Test all admin pages
4. Test all collector pages
5. Test API endpoints

---

## 🔧 Path Update Rules

### Before (Current):
```php
// Root files
require_once 'config.php';
require_once 'includes/auth.php';
header('Location: /Scrap/dashboard.php');

// Public admin files
require_once '../../includes/auth.php';
header('Location: /Scrap/public/admin/dashboard.php');
```

### After (Restructured):
```php
// Views/citizens files
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
header('Location: /Scrap/views/citizens/dashboard.php');

// Views/admin files
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
header('Location: /Scrap/views/admin/dashboard.php');
```

---

## 🎨 Benefits of New Structure

### 1. **Clear Separation of Concerns**
- ✅ Authentication pages isolated
- ✅ User type pages grouped together
- ✅ Easy to find specific functionality

### 2. **Consistent Organization**
- ✅ All view files in `/views/`
- ✅ All public assets in `/public/`
- ✅ Clear naming conventions

### 3. **Scalability**
- ✅ Easy to add new user types
- ✅ Easy to add new features per user type
- ✅ Better for team collaboration

### 4. **Security**
- ✅ Clear separation of public/private files
- ✅ Easier to configure access controls
- ✅ Assets properly isolated

### 5. **Maintenance**
- ✅ Easier to locate files
- ✅ Consistent path structure
- ✅ Better code organization

---

## ⚠️ Breaking Changes

### Files That Will Move:
- `/login.php` → `/views/auth/login.php`
- `/signup.php` → `/views/auth/signup.php`
- `/dashboard.php` → `/views/citizens/dashboard.php`
- `/profile.php` → `/views/citizens/profile.php`
- `/history.php` → `/views/citizens/history.php`
- `/request.php` → `/views/citizens/request.php`
- `/rewards.php` → `/views/citizens/rewards.php`
- `/map.php` → `/views/citizens/map.php`
- `/guide.php` → `/views/citizens/guide.php`
- `/public/admin/*` → `/views/admin/*`
- `/public/collectors/*` → `/views/collectors/*`
- `/uploads/` → `/public/uploads/`

### URLs That Will Change:
- `/Scrap/dashboard.php` → `/Scrap/views/citizens/dashboard.php`
- `/Scrap/public/admin/dashboard.php` → `/Scrap/views/admin/dashboard.php`
- `/Scrap/public/collectors/dashboard.php` → `/Scrap/views/collectors/dashboard.php`

### Solution: URL Rewriting
Use `.htaccess` to maintain backward compatibility:
```apache
# Redirect old URLs to new structure
RewriteRule ^dashboard\.php$ /views/citizens/dashboard.php [L]
RewriteRule ^public/admin/(.*)$ /views/admin/$1 [L]
RewriteRule ^public/collectors/(.*)$ /views/collectors/$1 [L]
```

---

## 🚀 Implementation Priority

### HIGH PRIORITY (Do First):
1. ✅ Create new directory structure
2. ✅ Update `includes/auth.php` with new path helpers
3. ✅ Create `.htaccess` for URL rewriting
4. ✅ Move and update authentication pages
5. ✅ Update navigation/header/sidebar includes

### MEDIUM PRIORITY (Do Second):
1. Move citizen pages
2. Move admin pages
3. Move collector pages
4. Update all internal links

### LOW PRIORITY (Do Last):
1. Move uploads folder
2. Organize CSS files
3. Clean up old files
4. Update documentation

---

## 📝 Helper Functions to Add

Add to `includes/auth.php`:

```php
/**
 * Get base path for views
 */
function getViewPath($type, $file) {
    $paths = [
        'auth' => '/views/auth/',
        'citizen' => '/views/citizens/',
        'admin' => '/views/admin/',
        'collector' => '/views/collectors/'
    ];
    
    return '/Scrap' . $paths[$type] . $file;
}

/**
 * Redirect to appropriate dashboard
 */
function redirectToDashboard() {
    if (isAdmin()) {
        header('Location: ' . getViewPath('admin', 'dashboard.php'));
    } elseif (isCollector()) {
        header('Location: ' . getViewPath('collector', 'dashboard.php'));
    } else {
        header('Location: ' . getViewPath('citizen', 'dashboard.php'));
    }
    exit;
}
```

---

## ✅ Testing Checklist

- [ ] Landing page loads correctly
- [ ] Login redirects to correct dashboard
- [ ] Signup creates account and redirects
- [ ] Citizen dashboard displays data
- [ ] Admin dashboard displays data
- [ ] Collector dashboard displays data
- [ ] All navigation links work
- [ ] All API calls function
- [ ] File uploads work
- [ ] Logout functions properly
- [ ] Session management intact
- [ ] Role-based access working

---

## 📌 Notes

- Keep `index.php` in root for easy access
- Keep `config.php` in root (referenced everywhere)
- API structure is good, don't change
- Consider implementing this in stages
- Test thoroughly after each phase
- Keep backup before starting
- Update README.md after completion

---

**Recommendation**: Implement Phase 1 first (create structure), test, then proceed with subsequent phases incrementally.
