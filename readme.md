# Simple Invite Codes

This was a quick project that has not been heavily tested by myself. Originally only shared as a [gist](https://gist.github.com/trepmal/2894109), but moved here for better updates and feedback.

---

1. Assumes "Anyone can register" is checked on wp-admin/options-general.php
1. Create a code by creating a post at `wp-admin/edit.php?post_type=invite_codes` with the code in the title
1. When a user registers at `wp-login.php?action=register`, they must provide a valid code. Code validation is fully dependent on `get_page_by_title`, so there is some loose matching
1. When a code is succuessfully used, the code "post" will be marked as 'draft', and post meta will be added indicating who used it.
1. Disabling the `wp_update_post` section will allow codes to be reused until manually marked as 'draft'

## CLI

```
wp sic generate [--count=<count>] [--chars=<chars>] [--length=<length>]
```

example:

```
$ wp sic generate --chars="abcdef12345" --length=4 --count=10
Created code 1a3c (ID: 27271)
Created code 2513 (ID: 27272)
Created code 2d13 (ID: 27273)
Created code d5bf (ID: 27274)
Created code f3a2 (ID: 27275)
Created code 1ad5 (ID: 27276)
Created code fcd5 (ID: 27277)
Created code 32bf (ID: 27278)
Created code e51f (ID: 27279)
Created code 21ed (ID: 27280)
```

See `wp help sic generate` for information on each option.


### Creating codes from a premade list

Suppose you have a predetermined list of codes you'd like to import. I recommend using core WP-CLI commands to import them.

For example, here's my `codes.txt` file, which contains 3 codes (they must each be on their own line):

```
specialcode1
specialcode2
specialcode3
```

I can then use this loop with WP-CLI to read that file and create new create the invite code posts:

```
$ while IFS=, read -r code; do wp post create --post_type=invite_codes --post_title=$code --post_status=publish; done < codes.txt
Success: Created post 27217.
Success: Created post 27218.
Success: Created post 27219.
```

Note that this method does not take into consideration duplicate codes.