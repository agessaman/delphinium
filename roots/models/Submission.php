<?php namespace Delphinium\Roots\Models;

class Submission
{   
    public $submission_id;
    public $assignment_id;
    public $course;
    public $attempt;
    public $body;
    public $grade;
    public $grade_matches_current_submission;
    public $html_url;
    public $preview_url;
    public $score;
    public $submission_comments;
    public $submission_type;
    public $submitted_at;
    public $url;
    public $user_id;
    public $grader_id;
    public $late;
    public $assignment_visible;
    
    public function toArray()
    {
        return (array)$this;
    }
}