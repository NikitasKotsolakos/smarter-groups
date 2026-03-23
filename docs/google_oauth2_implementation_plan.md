 Google OAuth2 Login Implementation Plan

 Overview

 Add "Login with Google" functionality to the existing Laravel Breeze authentication system using Laravel Socialite.

 ---
 Part 1: Google Cloud Console Setup (Your Steps)

 Step 1: Create a Google Cloud Project

 1. Go to https://console.cloud.google.com/
 2. Click the project dropdown at the top → "New Project"
 3. Enter a project name (e.g., "Smarter Groups") → Create

 Step 2: Enable Google+ API (or People API)

 1. Go to "APIs & Services" → "Library"
 2. Search for "Google+ API" or "People API"
 3. Click on it → Enable

 Step 3: Configure OAuth Consent Screen

 1. Go to "APIs & Services" → "OAuth consent screen"
 2. Select "External" (unless you have Google Workspace)
 3. Fill in required fields:
   - App name: Smarter Groups
   - User support email: Your email
   - Developer contact email: Your email
 4. Click "Save and Continue"
 5. On Scopes page, add:
   - email
   - profile
   - openid
 6. Save and continue through remaining steps

 Step 4: Create OAuth 2.0 Credentials

 1. Go to "APIs & Services" → "Credentials"
 2. Click "Create Credentials" → "OAuth client ID"
 3. Application type: Web application
 4. Name: "Smarter Groups Web Client"
 5. Add Authorized JavaScript origins:
   - http://localhost (development)
   - https://yourdomain.com (production)
 6. Add Authorized redirect URIs:
   - http://localhost/auth/google/callback (development)
   - https://yourdomain.com/auth/google/callback (production)
 7. Click "Create"
 8. Copy the Client ID and Client Secret - you'll need these for .env

 ---
 Part 2: Code Changes (My Implementation)

 1. Install Laravel Socialite

 composer require laravel/socialite

 2. Add Environment Variables

 File: .env (and .env.example)
 GOOGLE_CLIENT_ID=your-client-id
 GOOGLE_CLIENT_SECRET=your-client-secret
 GOOGLE_REDIRECT_URI=/auth/google/callback

 3. Configure Services

 File: config/services.php
 Add Google configuration:
 'google' => [
     'client_id' => env('GOOGLE_CLIENT_ID'),
     'client_secret' => env('GOOGLE_CLIENT_SECRET'),
     'redirect' => env('GOOGLE_REDIRECT_URI'),
 ],

 4. Database Migration

 Create migration to add google_id column to users table:
 php artisan make:migration add_google_id_to_users_table
 - Add nullable google_id string column
 - Make password column nullable (Google users won't have passwords initially)

 5. Update User Model

 File: app/Models/User.php
 - Add google_id to $fillable
 - Make password nullable in validation (for OAuth users)

 6. Create SocialAuthController

 File: app/Http/Controllers/Auth/SocialAuthController.php

 Methods:
 - redirectToGoogle() - Redirect user to Google OAuth
 - handleGoogleCallback() - Handle callback, create/find user, log in

 7. Add Routes

 File: routes/auth.php
 Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])
     ->name('auth.google');
 Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])
     ->name('auth.google.callback');

 8. Update Login/Register Views

 Files:
 - resources/views/auth/login.blade.php
 - resources/views/auth/register.blade.php

 Add a "Continue with Google" button with:
 - Visual separator ("or")
 - Google-branded button (white background, Google logo, appropriate styling)

 9. Create Blade Component for Social Login Button

 File: resources/views/components/social-login-button.blade.php
 - Reusable component for social login buttons
 - Include Google "G" logo SVG
 - Follow Google's branding guidelines

 10. Update User Factory

 File: database/factories/UserFactory.php
 - Add state for Google OAuth users (no password, has google_id)

 11. Write Tests

 File: tests/Feature/Auth/GoogleAuthTest.php
 - Test redirect to Google works
 - Test callback creates new user
 - Test callback logs in existing user (by email match)
 - Test callback logs in existing user (by google_id)

 ---
 Part 3: Login Experience Flow

 New User (First-time Google Login)

 1. User clicks "Continue with Google" on login or register page
 2. Redirected to Google OAuth consent screen
 3. User selects/confirms Google account
 4. Google redirects back to /auth/google/callback
 5. System creates new user with:
   - Name from Google profile
   - Email from Google profile
   - google_id set to Google's unique ID
   - email_verified_at set (Google verified their email)
   - No password (null)
 6. User is logged in and redirected to /workshops

 Existing User (Has Google Account Linked)

 1. User clicks "Continue with Google"
 2. Google OAuth flow
 3. System finds user by google_id
 4. User is logged in and redirected to /workshops

 Existing User (Email Match, No Google ID)

 1. User who previously registered with email/password clicks "Continue with Google"
 2. Google OAuth flow
 3. System finds user by email match
 4. System links google_id to existing account
 5. User is logged in and redirected to /workshops

 Edge Cases Handled

 - Email conflict: If Google email matches existing account, link accounts
 - Password-less users: Users who sign up via Google can later set a password via profile settings (optional future enhancement)

 ---
 Files to Create/Modify
 ┌───────────────────────────────────────────────────────────┬────────────────────────────────────┐
 │                           File                            │               Action               │
 ├───────────────────────────────────────────────────────────┼────────────────────────────────────┤
 │ composer.json                                             │ Modify (add socialite)             │
 ├───────────────────────────────────────────────────────────┼────────────────────────────────────┤
 │ .env                                                      │ Modify (add Google credentials)    │
 ├───────────────────────────────────────────────────────────┼────────────────────────────────────┤
 │ .env.example                                              │ Modify (add Google placeholders)   │
 ├───────────────────────────────────────────────────────────┼────────────────────────────────────┤
 │ config/services.php                                       │ Modify (add Google config)         │
 ├───────────────────────────────────────────────────────────┼────────────────────────────────────┤
 │ database/migrations/xxxx_add_google_id_to_users_table.php │ Create                             │
 ├───────────────────────────────────────────────────────────┼────────────────────────────────────┤
 │ app/Models/User.php                                       │ Modify (add google_id to fillable) │
 ├───────────────────────────────────────────────────────────┼────────────────────────────────────┤
 │ app/Http/Controllers/Auth/SocialAuthController.php        │ Create                             │
 ├───────────────────────────────────────────────────────────┼────────────────────────────────────┤
 │ routes/auth.php                                           │ Modify (add social auth routes)    │
 ├───────────────────────────────────────────────────────────┼────────────────────────────────────┤
 │ resources/views/auth/login.blade.php                      │ Modify (add Google button)         │
 ├───────────────────────────────────────────────────────────┼────────────────────────────────────┤
 │ resources/views/auth/register.blade.php                   │ Modify (add Google button)         │
 ├───────────────────────────────────────────────────────────┼────────────────────────────────────┤
 │ resources/views/components/social-login-button.blade.php  │ Create                             │
 ├───────────────────────────────────────────────────────────┼────────────────────────────────────┤
 │ database/factories/UserFactory.php                        │ Modify (add Google state)          │
 ├───────────────────────────────────────────────────────────┼────────────────────────────────────┤
 │ tests/Feature/Auth/GoogleAuthTest.php                     │ Create                             │
 └───────────────────────────────────────────────────────────┴────────────────────────────────────┘
 ---
 Verification

 1. Run migration: php artisan migrate
 2. Clear config cache: php artisan config:clear
 3. Build frontend: npm run build
 4. Run tests: php artisan config:clear && php artisan test --filter=GoogleAuth
 5. Manual testing:
   - Visit /login - see Google button
   - Click button - redirected to Google
   - Complete OAuth - redirected back and logged in
   - Check database for google_id populated
╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌