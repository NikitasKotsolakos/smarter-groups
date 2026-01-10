# Smarter Groups Style Guide

**Version:** 1.0
**Last Updated:** 2026-01-10
**Status:** Active

---

## Table of Contents

1. [Design Principles](#design-principles)
2. [Color Palette](#color-palette)
3. [Typography](#typography)
4. [Spacing System](#spacing-system)
5. [Component Styles](#component-styles)
6. [Layout Patterns](#layout-patterns)
7. [Dark Mode Strategy](#dark-mode-strategy)
8. [Accessibility Guidelines](#accessibility-guidelines)
9. [Icon Guidelines](#icon-guidelines)
10. [Animation & Transitions](#animation--transitions)

---

## Design Principles

### Core Values

1. **Professional Clarity**
   Smarter Groups is an educational tool for managing workshops and student assignments. The design should feel professional, trustworthy, and efficient - not playful or overly decorative.

2. **Functional Simplicity**
   Every element should serve a purpose. Remove visual clutter. Prioritize clear hierarchy and easy scanning of information.

3. **Consistent Reliability**
   Users should always know where they are and what actions are available. Consistent patterns reduce cognitive load and build confidence.

4. **Accessible by Default**
   Accessibility is not an afterthought. All color combinations must meet WCAG 2.1 AA standards. Keyboard navigation must work everywhere.

5. **Responsive Adaptability**
   The interface must work seamlessly from mobile phones to large desktop monitors. Tables and complex forms require special attention on smaller screens.

### Visual Hierarchy

- **Primary Actions:** Stand out with solid primary color backgrounds
- **Secondary Actions:** Outlined or muted backgrounds
- **Destructive Actions:** Red/danger color, require confirmation
- **Navigation:** Subtle, does not compete with content
- **Content:** Clean, readable, with clear section breaks

### Brand Personality

- **Tone:** Professional, helpful, efficient
- **Aesthetic:** Modern, clean, minimal gradients used sparingly for brand emphasis
- **Feel:** Trustworthy software for educators

---

## Color Palette

### Primary Colors

The primary brand uses an indigo-to-purple gradient, reflecting modern educational tools while maintaining professionalism.

| Token | Tailwind Class | Hex Value | Usage |
|-------|----------------|-----------|-------|
| Primary 50 | `bg-indigo-50` | #eef2ff | Hover backgrounds, subtle highlights |
| Primary 100 | `bg-indigo-100` | #e0e7ff | Selected states, light backgrounds |
| Primary 200 | `bg-indigo-200` | #c7d2fe | Borders on primary elements |
| Primary 300 | `bg-indigo-300` | #a5b4fc | Disabled primary states |
| Primary 400 | `bg-indigo-400` | #818cf8 | Links hover state |
| Primary 500 | `bg-indigo-500` | #6366f1 | Focus rings |
| **Primary 600** | `bg-indigo-600` | **#4f46e5** | **Primary buttons, active nav** |
| Primary 700 | `bg-indigo-700` | #4338ca | Primary hover state |
| Primary 800 | `bg-indigo-800` | #3730a3 | Primary active/pressed |
| Primary 900 | `bg-indigo-900` | #312e81 | Dark backgrounds |

### Secondary Colors (Accent)

Used sparingly for gradients and visual interest on the welcome page.

| Token | Tailwind Class | Hex Value | Usage |
|-------|----------------|-----------|-------|
| Secondary 500 | `bg-purple-500` | #a855f7 | Gradient end color |
| Secondary 600 | `bg-purple-600` | #9333ea | Accent elements |
| Secondary 700 | `bg-purple-700` | #7e22ce | Hover on accent |

### Semantic Colors

| Purpose | Tailwind Class | Hex Value | Light BG | Border |
|---------|----------------|-----------|----------|--------|
| **Success** | `text-green-700` | #15803d | `bg-green-50` | `border-green-200` |
| **Warning** | `text-yellow-700` | #a16207 | `bg-yellow-50` | `border-yellow-200` |
| **Error** | `text-red-700` | #b91c1c | `bg-red-50` | `border-red-200` |
| **Info** | `text-blue-700` | #1d4ed8 | `bg-blue-50` | `border-blue-200` |

### Neutral Colors

| Purpose | Tailwind Class | Hex Value |
|---------|----------------|-----------|
| Text Primary | `text-gray-900` | #111827 |
| Text Secondary | `text-gray-600` | #4b5563 |
| Text Muted | `text-gray-500` | #6b7280 |
| Text Disabled | `text-gray-400` | #9ca3af |
| Border Default | `border-gray-300` | #d1d5db |
| Border Light | `border-gray-200` | #e5e7eb |
| Background Page | `bg-gray-100` | #f3f4f6 |
| Background Card | `bg-white` | #ffffff |
| Background Header | `bg-gray-50` | #f9fafb |

### Brand Gradient

Used only on the welcome page hero and logo.

```css
/* Tailwind Classes */
bg-gradient-to-r from-indigo-600 to-purple-600

/* For text with gradient */
bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent
```

### Color Usage Rules

1. **Never use color alone** to convey information. Always pair with text, icons, or patterns.
2. **Primary color** is for interactive elements (buttons, links, focus states).
3. **Semantic colors** are for status and feedback only.
4. **Avoid pure black (#000)** for text. Use `gray-900` instead.
5. **Gradient usage** is limited to the welcome page hero and application logo.

---

## Typography

### Font Family

**Primary Font:** Inter
**Fallback Stack:** system-ui, sans-serif

```html
<!-- Font Loading (in layout files) -->
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
```

```javascript
// tailwind.config.js
fontFamily: {
    sans: ['Inter', ...defaultTheme.fontFamily.sans],
},
```

### Font Weights

| Weight | Tailwind Class | Usage |
|--------|----------------|-------|
| 400 (Regular) | `font-normal` | Body text, form inputs |
| 500 (Medium) | `font-medium` | Labels, navigation, subtle emphasis |
| 600 (Semibold) | `font-semibold` | Buttons, headings, important text |
| 700 (Bold) | `font-bold` | Hero text, page titles |

### Type Scale

| Element | Size | Weight | Line Height | Tailwind Classes |
|---------|------|--------|-------------|------------------|
| Hero Title | 48-60px | Bold | 1.1 | `text-5xl sm:text-6xl font-bold` |
| Page Title (h1) | 30px | Semibold | 1.25 | `text-3xl font-semibold` |
| Section Title (h2) | 24px | Semibold | 1.3 | `text-2xl font-semibold` |
| Subsection (h3) | 18px | Medium | 1.4 | `text-lg font-medium` |
| Card Title | 16px | Semibold | 1.5 | `text-base font-semibold` |
| Body Text | 16px | Normal | 1.5 | `text-base` |
| Small Text | 14px | Normal | 1.5 | `text-sm` |
| Caption/Helper | 12px | Normal | 1.5 | `text-xs` |
| Button Text | 12px | Semibold | 1 | `text-xs font-semibold uppercase tracking-widest` |
| Table Header | 12px | Medium | 1 | `text-xs font-medium uppercase tracking-wider` |

### Text Colors

| Purpose | Tailwind Class |
|---------|----------------|
| Primary text | `text-gray-900` |
| Secondary text | `text-gray-600` |
| Muted/placeholder | `text-gray-500` |
| Disabled text | `text-gray-400` |
| Link text | `text-indigo-600 hover:text-indigo-500` |
| Error text | `text-red-600` |

### Typography Rules

1. **Never use text smaller than 12px** (`text-xs`) for any content.
2. **Body text minimum** is 16px for readability.
3. **Line length** should not exceed 75 characters (use `max-w-prose` when appropriate).
4. **Use sentence case** for most text. Buttons use uppercase.
5. **Avoid long paragraphs** in application UI. Keep content scannable.

---

## Spacing System

### Base Unit

The spacing system is based on a 4px grid. All spacing values should be multiples of 4.

### Spacing Scale

| Tailwind | Pixels | Common Usage |
|----------|--------|--------------|
| `0` | 0px | No spacing |
| `px` | 1px | Borders |
| `0.5` | 2px | Tight inline spacing |
| `1` | 4px | Icon-to-text gap |
| `2` | 8px | Related elements |
| `3` | 12px | List item padding |
| `4` | 16px | Standard element padding |
| `5` | 20px | - |
| `6` | 24px | Section spacing |
| `8` | 32px | Between sections |
| `10` | 40px | - |
| `12` | 48px | Page section padding |
| `16` | 64px | Large section spacing |
| `20` | 80px | - |
| `24` | 96px | - |

### Spacing Patterns

**Form Fields:**
```blade
<!-- Between label and input -->
<x-input-label class="mb-1">Label</x-input-label>
<x-text-input />

<!-- Between form groups -->
<div class="mb-4">...</div>

<!-- Form sections -->
<div class="space-y-6">...</div>
```

**Cards and Containers:**
```blade
<!-- Card padding -->
<div class="p-6">...</div>

<!-- Page container -->
<div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">...</div>
```

**Lists and Tables:**
```blade
<!-- Table cells -->
<td class="px-6 py-4">...</td>

<!-- Table headers -->
<th class="px-6 py-3">...</th>

<!-- List items -->
<li class="py-3">...</li>
```

**Buttons:**
```blade
<!-- Standard button -->
<button class="px-4 py-2">...</button>

<!-- Button groups -->
<div class="flex gap-3">...</div>
```

### Spacing Rules

1. **Use gap utilities** instead of margins for flexbox/grid children.
2. **Consistent padding** within component types (all cards use `p-6`).
3. **Vertical rhythm** maintained with consistent `mb-*` values.
4. **Container max-width** is `max-w-4xl` for content, `max-w-7xl` for full layouts.

---

## Component Styles

### Buttons

#### Primary Button
Used for the main action on a page.

```blade
<x-primary-button>
    Save Changes
</x-primary-button>
```

**Classes:**
```css
inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent
rounded-md font-semibold text-xs text-white uppercase tracking-widest
hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none
focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
transition ease-in-out duration-150
```

#### Secondary Button
Used for secondary actions.

```blade
<x-secondary-button>
    Cancel
</x-secondary-button>
```

**Classes:**
```css
inline-flex items-center px-4 py-2 bg-white border border-gray-300
rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest
shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25
transition ease-in-out duration-150
```

#### Danger Button
Used for destructive actions.

```blade
<x-danger-button>
    Delete
</x-danger-button>
```

**Classes:**
```css
inline-flex items-center px-4 py-2 bg-red-600 border border-transparent
rounded-md font-semibold text-xs text-white uppercase tracking-widest
hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2
focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150
```

#### Button Link
Styled anchor tags for navigation actions.

```blade
<x-button-link href="{{ route('workshops.create') }}" variant="primary">
    Create Workshop
</x-button-link>
```

**Variants:** `primary`, `secondary`, `danger`

#### Button States

| State | Visual Change |
|-------|---------------|
| Default | Standard background |
| Hover | Slightly darker background |
| Focus | Ring appears around button |
| Active | Darker background |
| Disabled | 25% opacity, cursor not-allowed |

### Form Inputs

#### Text Input

```blade
<x-text-input
    type="text"
    name="name"
    class="block w-full"
    value="{{ old('name') }}"
/>
```

**Classes:**
```css
border-gray-300 focus:border-indigo-500 focus:ring-indigo-500
rounded-md shadow-sm
```

#### Input Label

```blade
<x-input-label for="name" :value="__('Workshop Name')" />
```

**Classes:**
```css
block font-medium text-sm text-gray-700
```

#### Input Error

```blade
<x-input-error :messages="$errors->get('name')" class="mt-2" />
```

**Classes:**
```css
text-sm text-red-600 space-y-1
```

#### Select

```blade
<x-select name="classroom" class="block w-full">
    <option value="">Select Classroom</option>
    @foreach($classrooms as $classroom)
        <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
    @endforeach
</x-select>
```

**Classes:**
```css
border-gray-300 focus:border-indigo-500 focus:ring-indigo-500
rounded-md shadow-sm
```

#### Form Field Pattern

```blade
<div class="mb-4">
    <x-input-label for="name" :value="__('Name')" />
    <x-text-input
        id="name"
        name="name"
        type="text"
        class="mt-1 block w-full"
        :value="old('name', $model->name ?? '')"
        required
    />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>
```

### Tables

#### Table Structure

```blade
<x-table>
    <x-table-header>
        <x-table-row :hover="false">
            <x-table-heading>Column 1</x-table-heading>
            <x-table-heading>Column 2</x-table-heading>
        </x-table-row>
    </x-table-header>
    <tbody class="bg-white divide-y divide-gray-200">
        @foreach($items as $item)
            <x-table-row>
                <x-table-data>{{ $item->name }}</x-table-data>
                <x-table-data>{{ $item->value }}</x-table-data>
            </x-table-row>
        @endforeach
    </tbody>
</x-table>
```

#### Table Component Classes

| Component | Classes |
|-----------|---------|
| Table wrapper | `overflow-x-auto rounded-lg shadow` |
| Table | `min-w-full divide-y divide-gray-200 border border-gray-200` |
| Header row | `bg-gray-50` |
| Header cell | `px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider` |
| Body row | `hover:bg-gray-50 transition-colors` |
| Body cell | `px-6 py-4 whitespace-nowrap text-sm text-gray-900` |

### Cards

```blade
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-2">Card Title</h3>
    <p class="text-gray-600">Card content goes here.</p>
</div>
```

**Standard Card Classes:**
```css
bg-white rounded-lg shadow-md border border-gray-200 p-6
```

**Feature Card (Welcome Page):**
```css
bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg
hover:shadow-xl transition-shadow duration-300
```

### Alerts / Notifications

#### Success Alert
```blade
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
    {{ session('success') }}
</div>
```

#### Error Alert
```blade
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
    @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
    @endforeach
</div>
```

### Modals

```blade
<x-modal name="confirm-deletion" focusable>
    <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">
            Confirm Deletion
        </h2>
        <p class="mt-3 text-sm text-gray-600">
            Are you sure you want to delete this item?
        </p>
        <div class="mt-6 flex justify-end gap-3">
            <x-secondary-button x-on:click="$dispatch('close')">
                Cancel
            </x-secondary-button>
            <x-danger-button>
                Delete
            </x-danger-button>
        </div>
    </div>
</x-modal>
```

### Tabs

```blade
<div class="border-b border-gray-200 mb-4">
    <nav class="-mb-px flex space-x-8">
        <button type="button"
            @click="setTab('groups')"
            :class="activeTab === 'groups'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
            Groups
        </button>
        <!-- More tabs... -->
    </nav>
</div>
```

### Navigation Links

#### Desktop Nav Link
```blade
<x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
    Dashboard
</x-nav-link>
```

**Active Classes:**
```css
border-b-2 border-indigo-400 text-gray-900
```

**Inactive Classes:**
```css
border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300
```

---

## Layout Patterns

### Page Container

```blade
<div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
    <!-- Page content -->
</div>
```

For full-width layouts:
```blade
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page content -->
</div>
```

### App Layout Structure

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Page Title
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Content -->
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

### Content Page Layout (Simplified)

Used for workshop pages without the standard header:

```blade
<x-app-layout>
    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold">Page Title</h2>
            <x-button-link href="{{ route('resource.create') }}" variant="primary">
                Action
            </x-button-link>
        </div>

        <!-- Content -->
    </div>
</x-app-layout>
```

### Form Layout

```blade
<form method="POST" action="{{ route('resource.store') }}">
    @csrf

    <div class="space-y-6">
        <!-- Form fields with mb-4 -->
        <div class="mb-4">
            <x-input-label />
            <x-text-input class="mt-1 block w-full" />
            <x-input-error class="mt-2" />
        </div>
    </div>

    <div class="mt-6 flex items-center gap-4">
        <x-primary-button>Save</x-primary-button>
        <x-button-link href="{{ route('resource.index') }}" variant="secondary">
            Cancel
        </x-button-link>
    </div>
</form>
```

### Grid Layouts

**2-Column Grid:**
```blade
<div class="grid md:grid-cols-2 gap-6">
    <div>Column 1</div>
    <div>Column 2</div>
</div>
```

**3-Column Feature Grid:**
```blade
<div class="grid md:grid-cols-3 gap-6">
    <div>Feature 1</div>
    <div>Feature 2</div>
    <div>Feature 3</div>
</div>
```

**4-Column Process Grid:**
```blade
<div class="grid md:grid-cols-4 gap-6">
    <div>Step 1</div>
    <div>Step 2</div>
    <div>Step 3</div>
    <div>Step 4</div>
</div>
```

### Responsive Breakpoints

| Breakpoint | Prefix | Min Width | Usage |
|------------|--------|-----------|-------|
| Mobile | (default) | 0px | Base styles |
| Small | `sm:` | 640px | Large phones, small tablets |
| Medium | `md:` | 768px | Tablets |
| Large | `lg:` | 1024px | Small laptops |
| Extra Large | `xl:` | 1280px | Desktops |
| 2XL | `2xl:` | 1536px | Large desktops |

### Responsive Patterns

**Container Padding:**
```blade
<div class="p-4 sm:p-6 lg:p-8">
```

**Grid Columns:**
```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
```

**Text Sizing:**
```blade
<h1 class="text-3xl sm:text-4xl lg:text-5xl">
```

---

## Dark Mode Strategy

### Current Implementation

Dark mode is currently implemented on the **welcome page only** using Tailwind's `dark:` variant. The application interior pages do not support dark mode.

### Dark Mode Classes (Welcome Page)

```blade
<!-- Background -->
<body class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">

<!-- Text -->
<span class="text-gray-900 dark:text-white">

<!-- Cards -->
<div class="bg-white dark:bg-gray-800">

<!-- Muted Text -->
<p class="text-gray-600 dark:text-gray-300">
<p class="text-gray-500 dark:text-gray-400">

<!-- Icon Backgrounds -->
<div class="bg-indigo-100 dark:bg-indigo-900">
<svg class="text-indigo-600 dark:text-indigo-400">
```

### Future Dark Mode Strategy

When dark mode is implemented application-wide:

1. **Use system preference** with `darkMode: 'class'` in tailwind.config.js
2. **Store user preference** in localStorage
3. **Add toggle** in navigation dropdown
4. **Apply consistently** to all pages and components

### Dark Mode Color Mapping

| Light Mode | Dark Mode |
|------------|-----------|
| `bg-white` | `dark:bg-gray-800` |
| `bg-gray-100` | `dark:bg-gray-900` |
| `bg-gray-50` | `dark:bg-gray-800` |
| `text-gray-900` | `dark:text-white` |
| `text-gray-700` | `dark:text-gray-300` |
| `text-gray-600` | `dark:text-gray-300` |
| `text-gray-500` | `dark:text-gray-400` |
| `border-gray-300` | `dark:border-gray-600` |
| `border-gray-200` | `dark:border-gray-700` |

---

## Accessibility Guidelines

### Color Contrast

All text must meet WCAG 2.1 AA standards:

| Text Size | Minimum Contrast Ratio |
|-----------|------------------------|
| Normal text (< 18px) | 4.5:1 |
| Large text (>= 18px bold or >= 24px) | 3:1 |
| UI components and graphics | 3:1 |

### Keyboard Navigation

1. **All interactive elements** must be focusable with Tab key
2. **Focus indicators** must be clearly visible (default ring styles)
3. **Logical tab order** follows visual layout
4. **Escape key** closes modals and dropdowns
5. **Enter/Space** activates buttons and links

### Focus Styles

```css
/* Default focus ring */
focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2

/* For inputs */
focus:border-indigo-500 focus:ring-indigo-500
```

### Form Accessibility

1. **All inputs must have labels** (use `<x-input-label>`)
2. **Error messages** must be associated with inputs
3. **Required fields** should be indicated visually and in markup
4. **Placeholder text** is not a substitute for labels

```blade
<!-- Proper form field -->
<div>
    <x-input-label for="email" :value="__('Email Address')" />
    <x-text-input
        id="email"
        name="email"
        type="email"
        required
        aria-describedby="email-error"
    />
    <x-input-error id="email-error" :messages="$errors->get('email')" />
</div>
```

### ARIA Usage

```blade
<!-- Modal with ARIA -->
<div role="dialog" aria-labelledby="modal-title" aria-modal="true">
    <h2 id="modal-title">Modal Title</h2>
</div>

<!-- Tabs with ARIA -->
<div role="tablist">
    <button role="tab" :aria-selected="activeTab === 'groups'" id="groups-tab">
        Groups
    </button>
</div>
<div role="tabpanel" aria-labelledby="groups-tab">
    <!-- Tab content -->
</div>

<!-- Alert with live region -->
<div role="alert" aria-live="polite">
    {{ session('success') }}
</div>
```

### Touch Targets

Minimum touch target size for mobile: **44x44 pixels**

```blade
<!-- Ensure buttons are large enough on mobile -->
<button class="p-2 min-w-[44px] min-h-[44px]">
```

---

## Icon Guidelines

### Icon Library

Use Heroicons (outline style) via inline SVG:

```blade
<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
</svg>
```

### Icon Sizing

| Context | Size | Tailwind Class |
|---------|------|----------------|
| Inline with text | 16px | `w-4 h-4` |
| Buttons | 20px | `w-5 h-5` |
| Standard icons | 24px | `w-6 h-6` |
| Feature icons | 24px | `w-6 h-6` |
| Large decorative | 48px | `w-12 h-12` |

### Icon Colors

- **Match text color:** `text-current` or inherit
- **Interactive icons:** `text-gray-500 hover:text-gray-700`
- **Decorative icons:** Match the semantic color context

### Icon Accessibility

```blade
<!-- Decorative icon (hidden from screen readers) -->
<svg aria-hidden="true">...</svg>

<!-- Meaningful icon (needs label) -->
<button aria-label="Delete item">
    <svg aria-hidden="true"><!-- trash icon --></svg>
</button>
```

---

## Animation & Transitions

### Transition Defaults

```css
/* Standard transition */
transition ease-in-out duration-150

/* For hover effects */
transition-colors duration-200

/* For shadows */
transition-shadow duration-300
```

### Common Animations

**Button Hover:**
```css
transition ease-in-out duration-150
```

**Card Hover:**
```css
hover:shadow-xl transition-shadow duration-300
```

**Modal Enter/Exit:**
```blade
x-transition:enter="ease-out duration-300"
x-transition:enter-start="opacity-0"
x-transition:enter-end="opacity-100"
x-transition:leave="ease-in duration-200"
x-transition:leave-start="opacity-100"
x-transition:leave-end="opacity-0"
```

**Dropdown:**
```blade
x-transition:enter="transition ease-out duration-200"
x-transition:enter-start="opacity-0 scale-95"
x-transition:enter-end="opacity-100 scale-100"
x-transition:leave="transition ease-in duration-75"
x-transition:leave-start="opacity-100 scale-100"
x-transition:leave-end="opacity-0 scale-95"
```

### Animation Guidelines

1. **Keep animations subtle** - they should enhance, not distract
2. **Duration under 300ms** for UI interactions
3. **Use ease-in-out** for most transitions
4. **Respect reduced motion** preferences (future enhancement)

```css
/* Future: respect reduced motion */
@media (prefers-reduced-motion: reduce) {
    * {
        transition-duration: 0.01ms !important;
        animation-duration: 0.01ms !important;
    }
}
```

---

## Quick Reference

### Component Checklist

When creating new components:

- [ ] Uses consistent spacing from the spacing scale
- [ ] Uses colors from the defined palette
- [ ] Has proper focus states
- [ ] Has hover states where appropriate
- [ ] Works with keyboard navigation
- [ ] Has appropriate ARIA labels if interactive
- [ ] Is responsive on mobile viewports
- [ ] Matches existing component patterns

### Tailwind Class Order Convention

Follow this order for maintainability:

1. Layout (flex, grid, block)
2. Positioning (relative, absolute)
3. Display/visibility
4. Sizing (w, h, min, max)
5. Spacing (m, p, gap)
6. Typography (font, text)
7. Background/borders
8. Effects (shadow, opacity)
9. Transitions/animations
10. States (hover, focus, active)
11. Responsive modifiers

Example:
```blade
<div class="flex items-center justify-between w-full p-4 text-sm text-gray-700 bg-white border border-gray-200 rounded-lg shadow-sm transition-shadow duration-200 hover:shadow-md">
```

---

## File References

### Component Files

| Component | Path |
|-----------|------|
| Primary Button | `resources/views/components/primary-button.blade.php` |
| Secondary Button | `resources/views/components/secondary-button.blade.php` |
| Danger Button | `resources/views/components/danger-button.blade.php` |
| Button Link | `resources/views/components/button-link.blade.php` |
| Text Input | `resources/views/components/text-input.blade.php` |
| Input Label | `resources/views/components/input-label.blade.php` |
| Input Error | `resources/views/components/input-error.blade.php` |
| Select | `resources/views/components/select.blade.php` |
| Table | `resources/views/components/table.blade.php` |
| Table Header | `resources/views/components/table-header.blade.php` |
| Table Row | `resources/views/components/table-row.blade.php` |
| Table Heading | `resources/views/components/table-heading.blade.php` |
| Table Data | `resources/views/components/table-data.blade.php` |
| Modal | `resources/views/components/modal.blade.php` |
| Dropdown | `resources/views/components/dropdown.blade.php` |
| Nav Link | `resources/views/components/nav-link.blade.php` |

### Configuration Files

| File | Purpose |
|------|---------|
| `tailwind.config.js` | Tailwind configuration including fonts |
| `resources/css/app.css` | Main CSS file with Tailwind directives |

### Layout Files

| Layout | Path |
|--------|------|
| App Layout | `resources/views/layouts/app.blade.php` |
| Guest Layout | `resources/views/layouts/guest.blade.php` |
| Navigation | `resources/views/layouts/navigation.blade.php` |

---

## Changelog

### Version 1.0 (2026-01-10)

- Initial style guide creation
- Documented existing design patterns from Phase 1 implementation
- Established color palette based on welcome page gradient theme
- Defined typography system with Inter font
- Created spacing and component documentation
- Added accessibility and layout guidelines

---

*This style guide is a living document and should be updated as the design system evolves.*
