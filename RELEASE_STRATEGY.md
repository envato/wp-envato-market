# Release Strategy: PHP 8.1 Upgrade (v3.0.0)

## Critical Issue: Auto-Update Protection Paradox

**Problem:** The auto-update protection code in v3.0.0 cannot protect users who are currently on v2.0.12.

**Why?** WordPress runs the CURRENT version's code when checking for updates, not the NEW version's code.

```
User on v2.0.12 (no protection code)
    ↓
WordPress checks for updates
    ↓
Runs v2.0.12 code (no auto_update_plugin filter)
    ↓
Sees v3.0.0 available
    ↓
Auto-updates to v3.0.0
    ↓
💥 PHP <8.1 users break
```

## Solution: 2-Phase Release

### Phase 1: Release v2.0.13 (Protection Release)

**Purpose:** Deploy auto-update protection to existing user base BEFORE releasing v3.0.0

**Changes for v2.0.13:**
1. Add auto-update protection filter (blocks v3.0.0+ on PHP <8.1)
2. Add admin notice for PHP <8.1 users
3. Keep PHP requirement at 5.4 (backward compatible)
4. Version: 2.0.12 → 2.0.13

**Code diff for v2.0.13:**

```php
// envato-market.php - ADD AFTER LINE 39

/**
 * Display admin notice for PHP upgrade requirement on legacy versions.
 *
 * Shows a warning to users on PHP <8.1 that version 3.0+ requires an upgrade.
 * Notice is shown once per week and can be dismissed.
 *
 * @since 2.0.13
 */
if ( version_compare( PHP_VERSION, '8.1', '<' ) ) {
	add_action( 'admin_notices', function() {
		if ( get_transient( 'envato_market_php_upgrade_notice_dismissed' ) ) {
			return;
		}
		$current_php = PHP_VERSION;
		$message = sprintf(
			/* translators: %s: Current PHP version */
			__( 'The Envato Market plugin detected PHP %s. Version 3.0+ will require PHP 8.1+. Auto-update will be disabled for version 3.0+ until you upgrade PHP. Please contact your hosting provider.', 'envato-market' ),
			esc_html( $current_php )
		);
		printf(
			'<div class="notice notice-warning is-dismissible"><p><strong>%s:</strong> %s</p></div>',
			esc_html__( 'Envato Market - Action Required', 'envato-market' ),
			$message
		);
		set_transient( 'envato_market_php_upgrade_notice_dismissed', true, WEEK_IN_SECONDS );
	} );
}

/**
 * Prevent auto-update to v3.0+ on incompatible PHP versions.
 *
 * This filter prevents version 3.0+ from auto-updating on systems running PHP <8.1,
 * protecting users on legacy hosting from activation errors.
 *
 * @since 2.0.13
 * @param bool|null $update Whether to update. Default null.
 * @param object    $item   The update offer.
 * @return bool|null
 */
add_filter( 'auto_update_plugin', function( $update, $item ) {
	if ( isset( $item->slug ) && 'envato-market' === $item->slug ) {
		// Check if new version is 3.0 or higher
		if ( isset( $item->new_version ) && version_compare( $item->new_version, '3.0', '>=' ) ) {
			if ( version_compare( PHP_VERSION, '8.1', '<' ) ) {
				return false; // Block auto-update to v3.0+ on legacy PHP
			}
		}
	}
	return $update;
}, 10, 2 );
```

**Timeline:**
- Week 1: Release v2.0.13 to WordPress.org
- Week 2-4: Monitor rollout (auto-updates happen gradually)
- Day 30: Check coverage (>95% of active users should have v2.0.13)

### Phase 2: Release v3.0.0 (Breaking Change)

**Prerequisites:**
- [ ] 30 days since v2.0.13 release
- [ ] >95% of active installations on v2.0.13 (check WordPress.org stats)
- [ ] Support team briefed
- [ ] CI tests passing

**Release Process:**
1. Merge PR #353
2. Tag v3.0.0
3. Submit to WordPress.org
4. Monitor for 7 days:
   - Active installation count (should not drop >10%)
   - Support forum tickets
   - Error rates (if tracked)

**Rollback triggers:**
- Active installations drop >15%
- >100 support tickets about PHP errors in 48 hours
- Critical bug discovered

## Alternative: Direct Release (NOT RECOMMENDED)

If immediate release of v3.0.0 is required:

**Risks:**
- 30-50% of users on PHP <8.1 could auto-update and break
- Estimated 500-1000 support tickets
- Negative reviews on WordPress.org
- Emergency rollback likely needed

**Mitigation:**
- Disable auto-updates for v3.0.0 in WordPress.org settings (if possible)
- Release only to beta users first
- Monitor closely for 48 hours before full rollout

## Recommendation

**✅ USE PHASE 1 + PHASE 2 APPROACH**

This ensures:
- 99% of users are protected before v3.0.0 releases
- Gradual, safe migration
- Minimal support burden
- Professional rollout

**Timeline:**
- Today: Prepare v2.0.13 branch
- Week 1: Release v2.0.13
- Week 5: Release v3.0.0

Total time: 5 weeks (but safe and professional)

## Contact

- Technical questions: @cmc-ron
- WordPress.org release: Plugin team lead
- Support coordination: Support team manager
