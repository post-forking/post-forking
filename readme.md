Post Forking Prototype
======================

Merging Issues
--------------
**How do we merge taxonomy changes?** 

*Example:* 

1. Post 1 has two tags, "red", and "blue". 
2. Post 1 is forked into post 2.
3. Aaron adds the tag "green" to post 2
4. Brian removes the tag "red" from post 1

How is 2 merged into 1? Do we need to do a three-way merge?

**How do we merge post_meta changes?**

Same as above, except metadata. 

*Also,* how to tell if single or multiple?

Stuff to figure out
-------------------

* Spoofing post_types so metaboxes, etc. appear
* Appending parent revision history to fork
* How to handle post_type_supports… opt in? opt out?
* Interacting with wp_text_diff
* Forking non-revisioned fields( post_status, post_date, etc. )

Possible Merging Solution
-------------------------

* Create a new post_type called post_snapshot
* Copy all post info to the snapshot **and** the fork
* Do a three way merge to try to resolve conflicts
* 