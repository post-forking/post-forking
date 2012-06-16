Post Forking for WordPress
==========================

Provides users that would not normally be able to edit a post with the ability to submit revisions. This can be users on a site without the `edit_post` or `edit_published_post` capabilities, or can be members of the general public.

Example Use
-----------



Terms
-----

* **Post** - Any WordPress post that uses the `post_content` field, including posts, pages, and custom post types
* **Fork** - Clone of a post intended for editing without disturbing the parent post
* **Branch** - Parallel versions of the same parent post, owned by the post author
* **Merge** - To push a fork's changes back into its parent post
* **Conflict** - When a post is forked if a given line is changed on the fork, and that same line is subsequently edited on the parent post prior to the merge, the post cannot be automatically merged, and the conflict is presented to the merger to resolve

Under the hood
--------------

Forking a post creates a copy of the most recent version of the post as a "fork" custom post type. Certain fields (e.g., `post_content`, `post_title`) are copied over to the new fork. We also store the revision ID for the revision prior to when the fork was created (see `includes/revisions.php` for more information as to why we store the previous revision). 

The fork post type has its own capabilities, allowing a user without the ability to edit or publish on the parent post to edit a fork. Once changes have been made, assuming the user does not have the `publish_fork` capability, the user would submit the fork for review (similar to submitting a Pull Request in GitHub parlance) using the normal WordPress moderation system.

Publishing a fork (either by the fork author, if they have the capability, or my an editor) triggers the merge itself. The post content of the fork undergoes a three way merge with the base revision and current version of the parent post.

A fork can have three post statuses:

1. Draft - The fork is being edited
1. Pending - The fork has been submitted for publication
1. Published - The fork has been merged

Note: No user should have the `edit_published_fork` capability. Once published, the fork post_type simply exists to provide a record of the change and allow the author page, to theoretically list contributions by author.

Future Features (Maybe)
-----------------------

* Ability to fork more than just the post_content (e.g., taxonomies, post meta)
* Appending parent revision history to fork
* Spoofing post_type so metaboxes, etc. appear
* Merge into WordPress core
 
Forking additional fields
-------------------------

As of this version, the only editable portion of the fork is the `post_content` field. The underlying logic has been built to be easily abstracted to accomidate forking of title, post meta, and taxonomies, but the logic for merging additional fields is not as clean. We'd need to create a snapshot of the post meta or taxonomy terms prior to the fork and then write the logic to do a three way merge of the changes. Complicating things further, for post meta, meta can be a single value or an array, furthing complicating a theoretical merge. Last, post_title would affect post_name which would break in the event of a conflict.
