# Database Migration Required

## ⚠️ Error: "Database error occurred" when suggesting drop-off point

### Cause
The `dropoff_points` table is missing two new columns:
- `photo_url` - stores the uploaded image path
- `suggested_by` - tracks which collector added the drop-off

### Solution: Run the Migration

#### Option 1: Using phpMyAdmin (Easiest)

1. **Open phpMyAdmin** in your browser:
   - URL: `http://localhost/phpmyadmin`
   - Or click "phpMyAdmin" in XAMPP control panel

2. **Select your database**:
   - Click `kiambu_recycling` in the left sidebar

3. **Open SQL tab**:
   - Click the "SQL" tab at the top

4. **Copy and paste this SQL**:
   ```sql
   -- Add photo_url column
   ALTER TABLE dropoff_points
   ADD COLUMN photo_url VARCHAR(255) NULL AFTER materials;

   -- Add suggested_by column
   ALTER TABLE dropoff_points
   ADD COLUMN suggested_by INT NULL AFTER photo_url;

   -- Add foreign key constraint
   ALTER TABLE dropoff_points
   ADD CONSTRAINT fk_dropoff_suggested_by 
   FOREIGN KEY (suggested_by) REFERENCES collectors(id) 
   ON DELETE SET NULL;

   -- Add index for faster lookups
   CREATE INDEX idx_dropoff_suggested_by ON dropoff_points(suggested_by);
   ```

5. **Click "Go"** button at the bottom

6. **Verify success**:
   - You should see: "2 rows affected" or "Query OK"
   - Green checkmark ✅

#### Option 2: Using MySQL Command Line

1. **Open Terminal/Command Prompt**

2. **Navigate to XAMPP MySQL bin**:
   ```bash
   cd /Applications/XAMPP/xamppfiles/bin
   ```

3. **Login to MySQL**:
   ```bash
   ./mysql -u root -p
   ```
   (Press Enter if no password, or type your password)

4. **Select database**:
   ```sql
   USE kiambu_recycling;
   ```

5. **Run migration file**:
   ```bash
   source /Applications/XAMPP/xamppfiles/htdocs/Scrap/sql/migrations/add_dropoff_photo_and_suggested_by.sql
   ```

   Or copy/paste the SQL commands from Option 1.

6. **Verify**:
   ```sql
   DESCRIBE dropoff_points;
   ```
   You should see `photo_url` and `suggested_by` in the list.

#### Option 3: Quick Terminal Command (macOS/Linux)

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/Scrap
/Applications/XAMPP/xamppfiles/bin/mysql -u root kiambu_recycling < sql/migrations/add_dropoff_photo_and_suggested_by.sql
```

### Verification

After running the migration, verify the columns exist:

**In phpMyAdmin:**
1. Click `kiambu_recycling` database
2. Click `dropoff_points` table
3. Click "Structure" tab
4. Look for `photo_url` and `suggested_by` columns

**Expected Result:**
```
Column Name    | Type         | Null | Key
---------------|--------------|------|-----
...
materials      | SET(...)     | NO   |
photo_url      | VARCHAR(255) | YES  |
suggested_by   | INT          | YES  | MUL
status         | ENUM(...)    | YES  |
created_at     | TIMESTAMP    | NO   |
```

### After Migration

1. **Refresh your browser** (F5)
2. **Try suggesting a drop-off point again**
3. Should now work! ✅

### Troubleshooting

**Error: "Column already exists"**
- The migration was already run. You're good to go!

**Error: "Cannot add foreign key constraint"**
- Make sure the `collectors` table has an `id` column
- Run this first: `ALTER TABLE collectors MODIFY id INT NOT NULL;`

**Error: "Access denied"**
- Use MySQL root user
- If password protected, add `-p` flag and enter password

**Error: "Table doesn't exist"**
- Make sure you selected the correct database: `USE kiambu_recycling;`
- Check database name in `/config.php`

### Migration File Location

The migration SQL file is saved at:
```
/Applications/XAMPP/xamppfiles/htdocs/Scrap/sql/migrations/add_dropoff_photo_and_suggested_by.sql
```

### What These Columns Do

**`photo_url` (VARCHAR 255, NULL):**
- Stores the path to uploaded drop-off point photos
- Example: `uploads/dropoffs/dropoff_1234567890_abc123.jpg`
- NULL if no photo uploaded

**`suggested_by` (INT, NULL):**
- Foreign key to `collectors.id`
- Tracks which collector suggested this drop-off point
- NULL for system-added or admin-added drop-offs
- Links to collector's name in admin view: "Added by: John Kamau"

---

**Run the migration now and try submitting your drop-off suggestion again!**
