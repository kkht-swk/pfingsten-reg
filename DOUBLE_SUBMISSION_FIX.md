# Double Form Submission Fix

## Problem

Users submitting team and player registration forms on mobile devices were creating duplicate entries. This happened because:
- Mobile users often double-tap or tap multiple times when pages load slowly
- No client-side protection prevented multiple button clicks
- No server-side duplicate detection was in place

## Solution

Implemented a two-layer protection system combining client-side and server-side safeguards.

---

## Changes Made

### 1. Client-Side Protection (`assets/app.js`)

**What it does:**
- Detects form submission on both `team_info` and `player_info` forms
- Immediately disables the submit button on first click
- Adds visual feedback (adds "..." to button text and reduces opacity to 0.6)
- Re-enables the button after 3 seconds (safety measure for validation errors)
- Prevents the button from being disabled multiple times

**Code added:**
```javascript
// Prevent double form submission for all forms
function preventDoubleSubmit(formSelector) {
    const form = document.querySelector(formSelector);
    if (form) {
        form.addEventListener('submit', function(event) {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton && !submitButton.disabled) {
                // Disable the button
                submitButton.disabled = true;
                // Add visual feedback
                const originalText = submitButton.textContent;
                submitButton.textContent = originalText + ' ...';
                submitButton.style.opacity = '0.6';

                // Re-enable after 3 seconds as a safety measure
                setTimeout(function() {
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                    submitButton.style.opacity = '1';
                }, 3000);
            }
        });
    }
}

// Apply to both team and player forms
preventDoubleSubmit('form[name="team_info"]');
preventDoubleSubmit('form[name="player_info"]');
```

**Benefits:**
- Prevents accidental double-taps on mobile devices
- Provides immediate visual feedback to users
- Works for fast network responses
- Doesn't interfere with form validation

---

### 2. Server-Side Protection - Team Registration (`src/Controller/TeamInfoController.php`)

**What it does:**
- Checks if a team with the same `verein` (club) and `altersklasse` (age category) was submitted in the last 10 seconds
- Only applies to new registrations (not edits with existing hashkey)
- If duplicate detected, redirects to the existing submission's summary page instead of creating a new entry
- Prevents duplicate database entries and duplicate emails

**Code added:**
```php
use DateTimeImmutable;  // Added to imports

// In the register() method, before saveTeamInfo():
if ($form->isSubmitted() && $form->isValid()) {
    // Check for duplicate submission
    if (!$hashkey) { // Only check for new registrations
        $recentSubmission = $repos->createQueryBuilder('t')
            ->where('t.verein = :verein')
            ->andWhere('t.altersklasse = :altersklasse')
            ->andWhere('t.createdAt > :recentTime')
            ->setParameter('verein', $ti->getVerein())
            ->setParameter('altersklasse', $ti->getAltersklasse())
            ->setParameter('recentTime', new DateTimeImmutable('-10 seconds'))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($recentSubmission) {
            // Duplicate detected - redirect to existing submission
            return $this->redirectToRoute('app_team_summary', [
                'hashkey' => $recentSubmission->getHashkey()
            ]);
        }
    }
    // ... continue with normal save
}
```

---

### 3. Server-Side Protection - Player Registration (`src/Controller/PlayerInfoController.php`)

**What it does:**
- Checks if a player with the same `vorname`, `nachname`, and `altersklasse` was submitted in the last 10 seconds
- If duplicate detected, redirects to the existing submission's summary page
- Prevents duplicate database entries and duplicate emails

**Code added:**
```php
use DateTimeImmutable;  // Added to imports

// In the new() method, before save():
if ($form->isSubmitted() && $form->isValid()) {
    // Check for duplicate submission
    $recentSubmission = $repos->createQueryBuilder('p')
        ->where('p.vorname = :vorname')
        ->andWhere('p.nachname = :nachname')
        ->andWhere('p.altersklasse = :altersklasse')
        ->andWhere('p.createdAt > :recentTime')
        ->setParameter('vorname', $pi->getVorname())
        ->setParameter('nachname', $pi->getNachname())
        ->setParameter('altersklasse', $pi->getAltersklasse())
        ->setParameter('recentTime', new DateTimeImmutable('-10 seconds'))
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();

    if ($recentSubmission) {
        // Duplicate detected - redirect to existing submission
        return $this->redirectToRoute('app_player_summary',
            ['hashkey' => $recentSubmission->getHashkey()]
        );
    }
    // ... continue with normal save
}
```

---

## Why This Works

### Client-Side Protection Handles:
- Double-tap on mobile devices
- Multiple clicks due to impatience
- Touch sensitivity issues
- Most common user errors

### Server-Side Protection Handles:
- Client-side JavaScript disabled or failing
- Network issues causing delayed responses
- Browser back/forward button usage
- Malicious users bypassing client-side checks
- Race conditions from simultaneous submissions

---

## Deployment Instructions

To deploy these changes to production:

```bash
# 1. Compile assets (includes the updated app.js)
bin/console asset-map:compile

# 2. Clear cache
bin/console cache:clear

# 3. Package and deploy as usual
tar czvf ../pf.tgz assets/ bin/ composer.json composer.lock config/ importmap.php LICENSE migrations/ public/ src/ templates/ translations/ .env.prod.local
scp ../pf.tgz <server>:
```

On the production server:
```bash
# Extract files
tar xzvf pf.tgz

# Compile assets
bin/console asset-map:compile

# Clear cache
bin/console cache:clear
```

---

## Configuration Notes

### Time Window: 10 Seconds

The 10-second window was chosen because:
- **Long enough** to catch rapid double-clicks and slow network responses
- **Short enough** to allow legitimate re-submissions if someone needs to correct data
- **Typical form submission** completes within 2-3 seconds

### Adjusting the Time Window

If you need to change the duplicate detection window, modify the `-10 seconds` parameter in both controllers:

```php
->setParameter('recentTime', new DateTimeImmutable('-10 seconds'))
// Change to '-5 seconds' for shorter window
// Change to '-30 seconds' for longer window
```

---

## Testing Recommendations

Before deploying, test the following scenarios:

1. **Normal single submission** (should work normally)
   - Fill form completely
   - Click submit once
   - Verify entry created correctly

2. **Rapid double-click on submit button** (should only create one entry)
   - Fill form completely
   - Quickly click submit button 2-3 times
   - Verify only one entry created
   - Check that only one email was sent

3. **Form with validation errors** (button should re-enable)
   - Leave required fields empty
   - Click submit
   - Wait 3 seconds
   - Verify button is clickable again

4. **Legitimate re-submission after delay** (should create new entry)
   - Submit a form successfully
   - Wait 15+ seconds
   - Submit the same data again
   - Verify a new entry is created (this is expected behavior)

5. **Edit existing team** (should not trigger duplicate detection)
   - Use edit link from email
   - Modify team data
   - Click submit
   - Verify changes are saved correctly

---

## Monitoring

After deployment, monitor:
- Number of team/player registrations
- Duplicate entries in database (should decrease significantly)
- User complaints about submission issues (should decrease)
- Duplicate confirmation emails (should stop)

Check logs for any patterns of duplicate detection:
```bash
# Look for teams/players being redirected to existing submissions
grep "createdAt" var/log/*.log
```

---

## Future Improvements (Optional)

If issues persist, consider:

1. **Token-based submission tracking**: Add a unique token to each form render
2. **Database constraints**: Add unique constraints on key fields (verein+altersklasse, vorname+nachname+altersklasse)
3. **Session-based tracking**: Store submitted form data in session to detect duplicates
4. **Increased time window**: Change from 10 to 30 seconds if needed
5. **More granular matching**: Include contact email in duplicate detection

---

## Rollback Plan

If this causes issues in production:

1. **Client-side only**: Comment out server-side duplicate detection
2. **Remove completely**: Revert to previous git commit
   ```bash
   git revert HEAD
   bin/console asset-map:compile
   bin/console cache:clear
   ```

---

## Date Implemented

2026-03-05

## Files Modified

- `assets/app.js`
- `src/Controller/TeamInfoController.php`
- `src/Controller/PlayerInfoController.php`
