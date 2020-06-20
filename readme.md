# Simple Invite Codes

This was a quick project that has not been heavily tested by myself. Originally only shared as a [gist](https://gist.github.com/trepmal/2894109), but moved here for better updates and feedback.

---

1. Assumes "Anyone can register" is checked on wp-admin/options-general.php
1. Create a code by creating a post at `wp-admin/edit.php?post_type=invite_codes` with the code in the title
1. When a user registers at `wp-login.php?action=register`, they must provide a valid code. Code validation is fully dependent on `get_page_by_title`, so there is some loose matching
1. When a code is succuessfully used, the code "post" will be marked as 'draft', and post meta will be added indicating who used it.
1. Disabling the `wp_update_post` section will allow codes to be reused until manually marked as 'draft'