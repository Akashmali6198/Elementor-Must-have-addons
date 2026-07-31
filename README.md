# Elementor Must-have Addons

**Elementor Must-have Addons** is a premium, lightweight WordPress plugin that extends the Elementor Page Builder with high-end, modern interactive widgets.

Developed by **Akash Mali** (<maliakash6198@gmail.com>).

---

## Features

### 1. 3D Video Scroll Widget
An interactive, scroll-controlled cinematic video section featuring fluid scene highlights. As visitors scroll down the page, the video is frame-scrubbed forward/backward, and matching textual content overlays transition seamlessly based on precise timings.
* **Scroll-to-Frame scrubbing** matching the exact video frames.
* **Dynamic Timed Scenes**: Add multiple overlays, change kicker, title, and trigger times inside the Elementor sidebar.
* **Vertical Navigator**: Displays frame progress and allows manual dragging or slider navigation.
* **Aesthetics Customization**: Adjust background color and gold accents.
* **Important Note**: A **15FPS** encoded video is recommended for optimal scroll synchronization and buttery-smooth frames (maximum recommended file size is **30MB** to ensure fast loading times).

### 2. Simple Submission Form Widget
A sleek, glassmorphic contact and submission form that captures user details without needing heavy contact form plugins.
* **Custom Field Repeater**: Build your own fields (text, email, textarea, tel) and set field validations.
* **Automatic Logging**: Submissions are logged locally to a dedicated secure table in your WordPress database.
* **Email Alerts**: Directly dispatches clean plain-text submission receipts to the specified admin email address.
* **Admin Viewer**: View, filter, and delete submissions from the plugin Settings panel.

### 3. Modular Addon Manager
To maintain optimal site performance, you can toggle individual addons ON or OFF from the settings dashboard under **Settings** -> **Must-have Addons**.

---

## Installation & Setup

1. Compress this folder into a `.zip` file or upload the folder directly to your `/wp-content/plugins/` directory.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Settings** -> **Must-have Addons** to enable or disable individual widgets.
4. Open any page in the **Elementor Editor**, search for "Must-have Addons", and drag the widgets onto your page!

---

## How It Works Under The Hood

### Video Scroll logic
* The widget outputs the video element and localized data containing the list of trigger timestamps.
* The JS library registers global `wheel`, `scroll`, and `resize` listeners, tracking scroll boundary metrics.
* When the widget enters the viewport, scrolling computes the vertical percentage. The percentage maps to `desiredTime = (progress * playbackEnd)`.
* It performs high-performance, non-blocking frame jumps by setting `video.currentTime` (throttled inside `requestAnimationFrame` at a target FPS like 15 frames per second).

### AJAX Form Submission
* The form uses custom jQuery-based AJAX submissions.
* Upon clicking submit, a POST request is sent to `admin-ajax.php` with a secure nonce token.
* The backend saves the serialized submission array to `wp_emha_submissions` database table, fetches the page's widget configurations (to obtain the custom recipient email address), and dispatches the alert using `wp_mail()`.

---

## Developer Contact
* **Author**: Akash Mali
* **Email**: maliakash6198@gmail.com
* **GitHub**: [Akashmali6198](https://github.com/Akashmali6198)
