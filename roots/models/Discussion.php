<?php namespace Delphinium\Roots\Models;

use Delphinium\Roots\Exceptions\InvalidParameterInRequestObjectException;

class Discussion
{   
    public $title;
    public $message;
    public $discussion_type;
    public $delayed_post_at;
    public $lock_at;
    public $podcast_enabled;
    public $require_initial_post;
    public $podcast_has_student_posts;
    public $is_announcement;
    public $group_category_id;
    public $published;
    
    function __construct($title, $message, $threaded,  $delayed_post_at, $lock_at, $podcast_enabled,$podcast_has_student_posts,
            $require_initial_post,$is_announcement, $published, $group_category_id = null) 
    {
        if(!is_string($title))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"title", "Title must be a string");
        }
        if(!is_string($message))
        {
            throw new InvalidParameterInRequestObjectException(get_class($this),"message", "Message must be a string");
        }
        
        $this->title = $title;
        $this->message = $message;
        $this->discussion_type = ($threaded)?"threaded":"side_comment";
        $this->delayed_post_at = $delayed_post_at;//($delayed_post_at) ? $delayed_post_at->format("c") : null;
        $this->lock_at = $lock_at;//($lock_at)?$lock_at->format("c"):null;
        $this->podcast_enabled = ($podcast_enabled)?true:false;
        $this->podcast_has_student_posts = ($podcast_has_student_posts)?true:false;
        $this->require_initial_post = ($require_initial_post)?true:false;
        $this->is_announcement = ($is_announcement)?true:false;
        $this->group_category_id = $group_category_id;
        $this->published = $published;
    }
}