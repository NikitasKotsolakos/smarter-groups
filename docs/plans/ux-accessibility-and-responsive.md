# Accessibility & Responsive Design

Two postponed phases from the original UX improvement work. Both are production-readiness polish: the app works today, but isn't great with a screen reader and isn't optimized for mobile.

The earlier UX phases (phantom CSS fixes, design system, component library, workshop UI refactor, loading/toast/transitions) are all shipped — see git history and `resources/views/components/` for the result.

## Accessibility (originally Phase 5)

**Goal:** keyboard navigation, screen reader support, focus management.

**Estimated effort (rough):** 8–10 hours.

Some pieces of this have already landed ad-hoc — there are `aria-label` / `sr-only` / `role="tab"` usages in `workshops/show.blade.php`, the tab partials, `components/alert.blade.php`, and `components/toast-container.blade.php`. The work below is what's still missing, plus a sweep to verify what's there.

### Tasks

1. **Audit existing ARIA usage.** Walk through every interactive element (tabs, modals, toasts, dropdowns, icon buttons) and confirm correctness against current WAI-ARIA practices. Flag and fix gaps.
2. **`aria-label` on every icon-only button.** Inline delete buttons in groups/classrooms/students tables, sort handles, anything else where the label is purely visual.
   ```blade
   <button type="button" aria-label="Delete group">
       <svg><!-- trash icon --></svg>
   </button>
   ```
3. **Skip-to-main-content link** in `app.blade.php` (and `guest.blade.php`) before the header.
   ```blade
   <a href="#main-content"
      class="sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0 focus:z-50 focus:px-4 focus:py-2 focus:bg-primary-600 focus:text-white">
       Skip to main content
   </a>
   ```
   Requires `id="main-content"` on the `<main>` element.
4. **ARIA live region** for dynamic notifications. The toast component already announces; verify it uses `aria-live="polite"` and `aria-atomic="true"` correctly. Add a global hidden `#status-messages` region for non-toast announcements (algorithm completion, save successes that don't pop a toast).
5. **Tab navigation ARIA states.** Ensure every tab in `workshops/show.blade.php` has `role="tab"`, `aria-selected`, and `tabindex` reflecting the active tab — currently tabs use Alpine but the ARIA wiring may be partial.
   ```blade
   <button type="button"
       role="tab"
       :aria-selected="activeTab === 'groups'"
       :tabindex="activeTab === 'groups' ? 0 : -1"
       @click="activeTab = 'groups'">
       Groups
   </button>
   ```
6. **Focus management** on modal open/close (return focus to trigger), on tab change (move focus to new panel), on toast appearance (no focus steal).
7. **Keyboard testing pass** — navigate the whole app with Tab / Shift+Tab / Enter / Space / Esc only, fix anything unreachable or trap-prone.

### Verification

- Run axe DevTools or Lighthouse a11y on every main page.
- Tab-only walkthrough of: workshop create, groups CRUD, run algorithm, manual reassign, delete-everything paths.
- Screen reader sanity check (VoiceOver / NVDA) on workshop show + assignments tab.

## Responsive Design (originally Phase 6)

**Goal:** make the app usable on phones — particularly the data tables that dominate the workshop view.

**Estimated effort (rough):** 6–8 hours.

Currently very little mobile work is in place — no `responsive-table` component, almost no `md:hidden` / `md:block` patterns. The desktop layout just shrinks awkwardly.

### Tasks

1. **Responsive table component.** Tables on the Groups / Classrooms / Students / Assignments tabs are the worst offenders on mobile. Convert to a desktop-table / mobile-card pattern via a shared component:
   ```blade
   {{-- resources/views/components/responsive-table.blade.php --}}
   <div class="hidden md:block">
       <x-table>{{ $desktop ?? $slot }}</x-table>
   </div>
   <div class="md:hidden space-y-4">
       {{ $mobile ?? $slot }}
   </div>
   ```
   Each tab partial needs a mobile card variant defining what to show per row.
2. **Touch target sizing.** Every interactive element minimum 44×44 px on mobile. Inline icon buttons especially need padding bumps:
   ```blade
   <button class="p-2 min-w-[44px] min-h-[44px] md:p-1 md:min-w-0 md:min-h-0">
       <!-- icon -->
   </button>
   ```
3. **Workshop view layout.** The tab strip and action bar at the top of `workshops/show.blade.php` may need vertical stacking or scroll-on-overflow on small screens.
4. **Forms.** Inputs and labels currently fine; the create/edit pages mostly work on mobile. Verify spacing and confirm the modal forms (delete confirmations, etc.) don't overflow.
5. **Navigation.** The `nav` already has a hamburger via Breeze. Verify it's still wired up and styled to match the rest of the design system.

### Verification

- Test on real device or Chrome DevTools device emulation: iPhone SE (375 px), iPhone 14 Pro (393 px), iPad (768 px).
- Walk through the full happy path on each viewport.
- Lighthouse mobile audit per main page.
