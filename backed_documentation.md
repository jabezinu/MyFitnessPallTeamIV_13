Fitness & Nutrition Tracking System - Database Design Presentation
Overview: What We're Building
Our fitness tracking system needs to store and manage:
User accounts and their personal information
Food consumption data with detailed nutrition tracking
Exercise activities for both cardio and strength training
Progress tracking through weight check-ins and goals
Administrative functions for system management
Let's walk through each component step by step.

1. USER MANAGEMENT SYSTEM
1.1 Users Table - The Foundation
users (
    user_id,           -- Unique identifier for each user
    email,             -- Login credential (must be unique)
    password_hash,     -- Encrypted password (never store plain text!)
    first_name,        -- Personal identification
    last_name,         -- Personal identification
    date_of_birth,     -- Needed for calorie calculations
    gender,            -- Affects metabolism calculations
    height_cm,         -- Required for BMR/calorie calculations
    activity_level,    -- Sedentary to very active (affects daily calorie needs)
    role,              -- 'user' or 'admin' (determines system access)
    is_active,         -- Can we disable accounts without deleting them
    created_at,        -- When did they join?
    last_login,        -- Security and usage tracking
    failed_login_attempts, -- Security: track failed logins
    account_locked_until   -- Security: temporary account locks
)

Why each field matters:
user_id: Primary key - every user needs a unique identifier
email: Must be unique because it's how users log in
password_hash: We NEVER store actual passwords, only encrypted versions
date_of_birth, gender, height_cm: These are essential for calculating how many calories a person should eat daily
activity_level: A sedentary person needs fewer calories than an athlete
role: Separates regular users from administrators
is_active: Allows us to "soft delete" - disable accounts without losing data
Security fields: Help us track and prevent unauthorized access
1.2 User Goals Table - What Are They Trying to Achieve?
user_goals (
    goal_id,              -- Unique identifier for each goal
    user_id,              -- Which user does this belong to?
    daily_calories_target, -- How many calories should they eat daily?
    daily_protein_target, -- Protein goal in grams
    daily_carbs_target,   -- Carbohydrate goal in grams
    daily_fat_target,     -- Fat goal in grams
    weight_goal_kg,       -- Target weight
    goal_type,            -- Are they trying to lose, gain, or maintain weight?
    target_date,          -- When do they want to reach their goal?
    is_active,            -- Users can have multiple goals over time
    created_at, updated_at -- When was this goal set/modified?
)

Why separate from users table:
Users might change their goals over time
We can track goal history
Different goals might overlap (weight goal + nutrition goals)
1.3 Weight Check-ins Table - Progress Tracking
weight_checkins (
    checkin_id,    -- Unique identifier
    user_id,       -- Which user?
    weight_kg,     -- Their weight on this date
    checkin_date,  -- What date was this recorded?
    notes,         -- Optional notes from user
    created_at     -- When was this data entered?
)

Why this approach:
One record per weigh-in allows us to track progress over time
Can generate charts showing weight changes
checkin_date is separate from created_at because users might enter yesterday's weight today

2. FOOD TRACKING SYSTEM
2.1 Food Categories Table - Organization
food_categories (
    category_id,    -- Unique identifier
    category_name,  -- "Fruits", "Vegetables", "Proteins", etc.
    description     -- Optional details about the category
)

Why categorize food:
Makes it easier for users to find food items
Helps with reporting (how much fruit vs vegetables are users eating?)
Allows filtering in the user interface
2.2 Food Items Table - The Food Database
food_items (
    food_id,                -- Unique identifier
    food_name,              -- "Apple", "Chicken Breast", "White Rice"
    brand,                  -- "Dole", "Tyson", etc. (optional)
    category_id,            -- Links to food_categories table
    serving_size,           -- "1 medium apple", "100g", "1 cup"
    serving_unit,           -- "piece", "gram", "cup"
    calories_per_serving,   -- How many calories in one serving
    protein_per_serving,    -- Protein in grams
    carbs_per_serving,      -- Carbohydrates in grams
    fat_per_serving,        -- Fat in grams
    fiber_per_serving,      -- Fiber in grams
    is_verified,            -- Has an admin approved this food item?
    is_public,              -- Can all users see this, or just the creator?
    created_by_user_id,     -- Which user added this food? (NULL for admin-added)
    created_at, updated_at  -- Tracking timestamps
)

Why so much detail:
serving_size + serving_unit: "1 cup" means different amounts for different foods
Multiple nutrition fields: Users want to track more than just calories
is_verified: User-created foods need admin approval before everyone can use them
created_by_user_id: Users can create custom foods (like homemade recipes)
2.3 Food Diary Entries - What Did They Actually Eat?
food_diary_entries (
    entry_id,           -- Unique identifier
    user_id,            -- Which user ate this?
    food_id,            -- What food did they eat? (links to food_items)
    meal_type,          -- breakfast, lunch, dinner, or snack
    serving_amount,     -- How many servings? (1.5 apples, 0.75 cups rice)
    entry_date,         -- What date was this eaten?
    calories_consumed,  -- Calculated: food calories × serving_amount
    protein_consumed,   -- Calculated: food protein × serving_amount
    carbs_consumed,     -- Calculated: food carbs × serving_amount
    fat_consumed,       -- Calculated: food fat × serving_amount
    created_at, updated_at -- When was this logged?
)

Why store calculated values:
Performance: Instead of calculating calories every time we need them, we store the result
Data integrity: What if the food item's calories change later? We want to keep historical accuracy
serving_amount: Users rarely eat exactly one serving (they might eat 1.5 apples)
2.4 Quick Food Entries - When Food Isn't in Database
quick_food_entries (
    quick_entry_id, -- Unique identifier
    user_id,        -- Which user?
    food_name,      -- "Homemade pizza slice"
    meal_type,      -- breakfast, lunch, dinner, snack
    calories,       -- User's estimate
    entry_date,     -- When was this eaten?
    notes,          -- Optional details
    created_at      -- When was this logged?
)

Why separate table:
Sometimes users eat something not in our database
They should be able to quickly log calories without creating a full food item
Keeps the main food_diary_entries table clean

3. EXERCISE TRACKING SYSTEM
3.1 Exercise Categories - Organization
exercise_categories (
    category_id,     -- Unique identifier
    category_name,   -- "Running", "Weight Training", "Yoga"
    category_type,   -- cardiovascular, strength, flexibility, sports
    description      -- Optional details
)

Purpose: Same as food categories - organization and filtering
3.2 Exercise Database - Available Exercises
exercise_database (
    exercise_id,         -- Unique identifier
    exercise_name,       -- "Push-ups", "Bench Press", "Running"
    category_id,         -- Links to exercise_categories
    exercise_type,       -- cardiovascular, strength, flexibility, sports
    calories_per_minute, -- Estimated calorie burn per minute
    description,         -- What is this exercise?
    instructions,        -- How to perform it properly
    muscle_groups,       -- JSON: ["chest", "shoulders", "triceps"]
    equipment_needed,    -- "dumbbells", "barbell", "none"
    difficulty_level,    -- beginner, intermediate, advanced
    is_verified,         -- Admin approved?
    is_public,           -- Available to all users?
    created_by_user_id,  -- Which user created this? (NULL for admin)
    created_at, updated_at
)

Key design decisions:
muscle_groups as JSON: Exercises can work multiple muscle groups
calories_per_minute: Base rate, actual burn depends on user's weight and intensity
User-created exercises: Users can add their own exercises
3.3 Cardiovascular Exercise Entries
cardio_exercise_entries (
    entry_id,         -- Unique identifier
    user_id,          -- Which user?
    exercise_id,      -- What exercise? (links to exercise_database)
    entry_date,       -- When was this performed?
    duration_minutes, -- How long did they exercise?
    calories_burned,  -- Calculated based on duration, user weight, intensity
    distance,         -- How far did they go? (optional)
    distance_unit,    -- km, miles, meters
    intensity_level,  -- low, moderate, high
    notes,            -- Optional user notes
    created_at, updated_at
)

Why separate from strength training:
Cardio and strength training have completely different metrics
Cardio focuses on: duration, distance, calories burned
This keeps queries fast and fields relevant
3.4 Strength Training Exercise Entries
strength_exercise_entries (
    entry_id,         -- Unique identifier
    user_id,          -- Which user?
    exercise_id,      -- What exercise?
    entry_date,       -- When was this performed?
    sets,             -- How many sets?
    reps_per_set,     -- JSON: [12, 10, 8] (reps for each set)
    weight_per_set,   -- JSON: [50, 55, 60] (weight for each set in kg)
    weight_unit,      -- kg or lbs
    rest_time_seconds,-- How long did they rest between sets?
    calories_burned,  -- Estimated calories burned
    notes,            -- Optional user notes
    created_at, updated_at
)

Why JSON for reps and weights:
Each set might have different reps: Set 1: 12 reps, Set 2: 10 reps, Set 3: 8 reps
Progressive overload: Set 1: 50kg, Set 2: 55kg, Set 3: 60kg
JSON allows flexibility without creating separate tables
3.5 Quick Exercise Entries
quick_exercise_entries (
    quick_entry_id,   -- Unique identifier
    user_id,          -- Which user?
    exercise_name,    -- "Basketball pickup game"
    exercise_type,    -- cardiovascular, strength, other
    duration_minutes, -- How long?
    calories_burned,  -- User's estimate
    entry_date,       -- When was this performed?
    notes,            -- Optional details
    created_at
)

Purpose: Similar to quick food entries - for activities not in our database

4. SECURITY & SESSION MANAGEMENT
4.1 User Sessions Table
user_sessions (
    session_id,    -- Unique session identifier (long random string)
    user_id,       -- Which user does this session belong to?
    ip_address,    -- Where are they connecting from?
    user_agent,    -- What browser/device are they using?
    created_at,    -- When did this session start?
    expires_at,    -- When will this session expire?
    is_active      -- Is this session still valid?
)

Why track sessions:
Security: We can see all active sessions for a user
Users can log out from all devices
We can detect suspicious activity (multiple locations)
4.2 Login Attempts Table
login_attempts (
    attempt_id,   -- Unique identifier
    email,        -- What email tried to log in?
    ip_address,   -- From where?
    success,      -- Did the login succeed?
    attempted_at, -- When did this happen?
    user_agent    -- What device/browser?
)

Security benefits:
Track failed login attempts (potential attacks)
Identify patterns of suspicious activity
Help users see their login history

5. SYSTEM ADMINISTRATION
5.1 User Notifications Table
user_notifications (
    notification_id,    -- Unique identifier
    user_id,            -- Which user should see this?
    title,              -- "Goal Achievement!" or "Daily Reminder"
    message,            -- Full notification text
    notification_type,  -- reminder, achievement, system, warning
    is_read,            -- Has the user seen this?
    created_at,         -- When was this notification created?
    read_at             -- When did the user read it?
)

Use cases:
Daily reminders to log food
Congratulations on reaching goals
System maintenance notifications
Warning about account issues
5.2 System Settings Table
system_settings (
    setting_id,         -- Unique identifier
    setting_key,        -- "max_login_attempts", "session_timeout"
    setting_value,      -- "5", "24"
    description,        -- What does this setting do?
    updated_by_user_id, -- Which admin changed this?
    updated_at          -- When was it changed?
)

Why centralize settings:
Admins can change system behavior without code changes
Track who changed what and when
Easy to backup and restore configurations

6. DATABASE RELATIONSHIPS EXPLAINED
Primary Relationships:
Users → Goals: One user can have multiple goals over time
Users → Weight Check-ins: One user, many weigh-ins
Users → Food Diary: One user, many food entries
Users → Exercise Entries: One user, many workout logs
Food Items → Food Diary: One food can be eaten by many users, many times
Exercise Database → Exercise Entries: One exercise can be performed by many users
Why This Design Works:
Normalization: No duplicate data (food nutrition stored once, used many times)
Flexibility: Users can create custom foods and exercises
Performance: Strategic indexes on commonly queried fields
Security: Proper foreign key constraints prevent orphaned data
Scalability: Can handle millions of users and entries

7. PERFORMANCE CONSIDERATIONS
Strategic Indexing:
-- Most important indexes
CREATE INDEX idx_food_diary_user_date ON food_diary_entries(user_id, entry_date);
CREATE INDEX idx_cardio_user_date ON cardio_exercise_entries(user_id, entry_date);
CREATE INDEX idx_weight_user_date ON weight_checkins(user_id, checkin_date);

Why these indexes:
Users will frequently query "show me my food diary for this week"
Date-based queries are very common in fitness apps
Multi-column indexes speed up the most frequent query patterns

8. REAL-WORLD USAGE EXAMPLES
Example 1: User Logs Breakfast
User selects "Oatmeal" from food_items table
System creates record in food_diary_entries:
food_id = 123 (oatmeal)
serving_amount = 1.5 (they ate 1.5 servings)
calories_consumed = 150 × 1.5 = 225
meal_type = 'breakfast'
Example 2: User Does a Workout
User selects "Bench Press" from exercise_database
System creates record in strength_exercise_entries:
exercise_id = 456
sets = 3
reps_per_set = [12, 10, 8]
weight_per_set = [70, 75, 80]
Example 3: Generate Progress Report
SELECT 
    checkin_date,
    weight_kg,
    (weight_kg - LAG(weight_kg) OVER (ORDER BY checkin_date)) as weight_change
FROM weight_checkins 
WHERE user_id = 123 
ORDER BY checkin_date;


9. ADMIN DASHBOARD CAPABILITIES
This database design supports all admin requirements:
User Management:
View all users, their activity levels, last login
Disable accounts (set is_active = FALSE)
Monitor login attempts and security issues
Content Moderation:
Review user-created foods (is_verified = FALSE)
Approve or reject exercise submissions
Ensure data quality across the system
System Analytics:
Most popular foods and exercises
User engagement metrics
System usage patterns
Error tracking and resolution

10. SCALABILITY & FUTURE-PROOFING
This Design Handles Growth:
Partitioning Ready: Date-based tables can be partitioned by month/year
Microservices Ready: Clear boundaries between user management, food tracking, and exercise tracking
API Ready: Clean data structure supports REST API development
Mobile Ready: Efficient queries support mobile app performance requirements
Easy Extensions:
Add new nutrition fields to food_items
Add new exercise types without schema changes
Implement social features (user friends, sharing)
Add premium features with user subscription tracking

CONCLUSION
This database design provides: 
✅ Complete SRS Coverage: Every requirement is addressed 
✅ Security First: Proper authentication and authorization 
✅ Performance Optimized: Strategic indexing and efficient queries 
✅ User Friendly: Supports both power users and casual users 
✅ Admin Friendly: Comprehensive management and reporting capabilities 
✅ Future Proof: Scalable and extensible design
The design balances complexity with usability, ensuring that both users and administrators have all the tools they need while maintaining excellent performance and security standards.

