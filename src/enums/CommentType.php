<?php
namespace selvinortiz\enums;

/**
 * Class CommentType
 *
 * @package selvinortiz\enums
 */
abstract class CommentType
{
    const Reply       = 'reply';        // Reply to a top-level forum post
    const Signup      = 'signup';       // A new user account
    const Message     = 'message';      // A message between just a few users
    const Comment     = 'comment';      // A blog comment
    const ContactForm = 'contact-form'; // A contact or feedback form submission
    const ForumPost   = 'forum-post';   // A top-level forum post
    const BlogPost    = 'blog-post';    // A blog post
}
