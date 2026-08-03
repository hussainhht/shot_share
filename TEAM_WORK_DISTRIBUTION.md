# Team Work Distribution

This project will be developed by a team of **three members**.
Each member is responsible for a complete group of related features, including the frontend, backend, validation, and testing of their assigned work.

---

## Member 1 — User Authentication and Profile Management

### Main Responsibilities

- Create the database connection file.
- Create the user registration page.
- Create the login page.
- Create the logout functionality.
- Manage user sessions and cookies.
- Protect pages that require authentication.
- Create the Edit Profile page.
- Allow users to update their full name.
- Allow users to securely change their password.

### Required Validation and Security

- Validate the full name.
- Validate the email address.
- Prevent duplicate email registration.
- Validate password strength.
- Confirm that password fields match.
- Hash passwords before saving them in the database.
- Verify hashed passwords during login.
- Use prepared statements for database queries.
- Redirect unauthenticated users away from protected pages.

### Suggested Files

```text
config/
└── database.php

auth/
├── register.php
├── login.php
└── logout.php

profile/
└── edit.php

includes/
└── auth.php
```

### Completion Checklist

- [X] A new user can register.
- [X] Duplicate emails are rejected.
- [X] Passwords are stored securely as hashes.
- [X] Registered users can log in.
- [ ] Logged-in users can log out.
- [ ] Protected pages cannot be opened without login.
- [ ] Users can update their full name.
- [ ] Users can securely change their password.
- [ ] Error and success messages are displayed correctly.

---

## Member 2 — Post Creation, Image Upload, Post Details, and Deletion

### Main Responsibilities

- Create the Create Post page.
- Allow authenticated users to write a post.
- Allow users to upload an image with the post.
- Save the post information in the database.
- Save the uploaded image path in the database.
- Create the Post Details page.
- Display the post author, text, image, and timestamp.
- Add post deletion functionality.
- Ensure users can delete only their own posts.
- Add a JavaScript image preview or delete confirmation.

### Required Validation and Security

- Allow only authenticated users to create posts.
- Validate that the post text is not empty.
- Accept only JPG, JPEG, and PNG images.
- Validate the image type on the server.
- Validate the image size.
- Generate a safe and unique image filename.
- Prevent users from deleting posts belonging to other users.
- Use prepared statements for all post queries.
- Escape displayed post content before showing it in HTML.

### Suggested Files

```text
posts/
├── create.php
├── view.php
└── delete.php

uploads/
└── posts/

assets/
└── js/
    ├── image-preview.js
    └── delete-confirmation.js
```

### Completion Checklist

- [ ] A logged-in user can create a post.
- [ ] The post text is saved in the database.
- [ ] A valid image can be uploaded.
- [ ] Invalid image formats are rejected.
- [ ] The uploaded image path is saved correctly.
- [ ] A post can be opened on a separate details page.
- [ ] The post author and timestamp are displayed.
- [ ] The post owner can delete their post.
- [ ] Other users cannot delete that post.
- [ ] JavaScript image preview or delete confirmation works.

---

## Member 3 — Home Feed, Search, Shared Layout, and Website Design

### Main Responsibilities

- Create the Home Page and Global Feed.
- Display posts from all users.
- Sort posts from newest to oldest.
- Display the author name, post text, image, and timestamp.
- Link each post preview to its Post Details page.
- Create the Search page.
- Search for posts using keywords or phrases.
- Create the shared navigation bar.
- Create the shared header and footer.
- Create the main website styling.
- Make the website responsive.
- Use Bootstrap, Tailwind CSS, Bulma, or another approved template/framework.
- Implement AJAX live search if selected as the JavaScript feature.
- Display shared success and error messages.

### Required Validation and Behavior

- Show the newest posts first.
- Show a welcome message for the logged-in user.
- Display the correct navigation options based on login status.
- Ensure search results match text inside posts.
- Escape displayed database content.
- Ensure the website works on mobile and desktop screens.
- Keep the design consistent across all pages.

### Suggested Files

```text
index.php

search/
├── index.php
└── search-api.php

includes/
├── header.php
├── navbar.php
├── footer.php
└── messages.php

assets/
├── css/
│   └── style.css
└── js/
    ├── main.js
    └── search.js
```

### Completion Checklist

- [ ] The Home Page displays posts from all users.
- [ ] The newest post appears first.
- [ ] Each post preview links to its details page.
- [ ] The logged-in user sees a welcome message.
- [ ] The navigation bar works correctly.
- [ ] Users can search for posts.
- [ ] Search results are accurate.
- [ ] AJAX live search works if selected.
- [ ] The website has a consistent design.
- [ ] The website is responsive on mobile and desktop.

---

# Shared Team Responsibilities

The following tasks must be completed and reviewed by all three members:

## Project Setup

- Agree on the project folder structure.
- Agree on file and folder naming.
- Create the `users` and `posts` database tables.
- Agree on database column names before coding.
- Select the CSS framework or HTML template.
- Agree on the website colors and general design.

## Integration

- Connect authentication with post creation.
- Connect users with their posts.
- Connect the Home Feed with the Post Details page.
- Connect search results with the Post Details page.
- Make sure shared files work correctly on every page.
- Resolve conflicts between different project sections.

## Testing

- Test registration with valid and invalid data.
- Test duplicate email registration.
- Test login with correct and incorrect passwords.
- Test protected pages without logging in.
- Test creating posts with and without images.
- Test invalid image uploads.
- Test post deletion by the owner.
- Test attempts to delete another user's post.
- Test profile updates.
- Test search using different keywords.
- Test the website on mobile and desktop screens.
- Test that passwords are hashed in the database.
- Test that SQL queries use prepared statements.
- Test that displayed user content is escaped safely.

## Documentation

- Update the README when features are completed.
- Add setup and database import instructions.
- Add screenshots of the final website.
- List all implemented features.
- Explain the selected JavaScript feature.
- Prepare the final project demonstration.

---

# Work Summary

| Team Member | Main Area                | Main Features                                                |
| ----------- | ------------------------ | ------------------------------------------------------------ |
| Member 1    | Users and Security       | Registration, Login, Logout, Sessions, Cookies, Edit Profile |
| Member 2    | Post Management          | Create Post, Image Upload, Post Details, Delete Post         |
| Member 3    | Feed, Search, and Design | Global Feed, Search, AJAX, Navigation, Responsive UI         |

---

# Important Team Rule

Each member is responsible for:

1. Writing the frontend for their assigned pages.
2. Writing the PHP backend for their assigned features.
3. Adding server-side validation.
4. Testing their work before integration.
5. Explaining their code during the final presentation.

No member should work only on HTML, only on CSS, or only on PHP.
Every member must understand and complete the full functionality of their assigned section.
