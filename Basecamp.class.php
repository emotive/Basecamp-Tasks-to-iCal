<?php

/**
 * Project:     Basecamp PHP API
 * File:        Basecamp.class.php
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * Smarty mailing list. Send a blank e-mail to
 * smarty-discussion-subscribe@googlegroups.com 
 *
 * @link http://www.rebel-interactive.com/
 * @copyright 2009 REBEL INTERACTIVE, Inc.
 * @author Monte Ohrt <monte at ohrt dot com>
 * @package BasecampPHPAPI
 * @version 1.1.1-dev
 */

/* $Id$ */

/*
 * The Basecamp PHP API requires the RestRequest library class, which
 * comes bundled with this library. RestRequest can easily be used for
 * other PHP REST projects.
 */


require('RestRequest.class.php');

class Basecamp {
  
  /**
   * The REST request object
   *
   * @var object
   */
  protected $request;
  
  /**
   * The base URL.
   * example:
   * http://mycompany.basecamphq.com/
   *
   * @var string
   */
  protected $baseurl;

  /**
   * The returned data format.
   * possible values:
   * <ul>
   *  <li>simplexml -> return a SimpleXMLElement PHP object (default)</li>
   *  <li>xml -> return an XML string</li>
   * </ul>
   *
   * @var string
   */
  protected $format;

  /**
   * The API login username
   *
   * @var string
   */
  protected $username;
  
  /**
   * The API login password
   *
   * @var string
   */
  protected $password;

  /**
   * The body of the API request
   *
   * @var string
   */
  protected $request_body;

  
  /**#@-*/
  /**
   * The class constructor.
   */
  public function __construct ($baseurl,$username=null,$password=null,$format='xml') {
    $this->setBaseurl($baseurl);
    $this->setFormat($format);
    $this->setUsername($username);
    $this->setPassword($password);
    $this->setFormat($format);
  }
 
  /* public methods */
  
  /**
   * gets all projects
   *
   * @return array response content
   */  
  public function getProjects() {
    return $this->processRequest("{$this->baseurl}projects.xml","GET");
  }

  /**
   * get a single project
   *
   * @param int $project_id
   * @return array response content
   */  
  public function getProject($project_id) {
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
    return $this->processRequest("{$this->baseurl}projects/{$project_id}.xml","GET");
  }
  
  /**
   * get info for logged in user
   *
   * @param string $format format of response (opt)
   * @return array response content
   */
  public function getMe($format=null) {
    return $this->processRequest("{$this->baseurl}me.xml","GET",$format);
  }

  /**
   * get id for logged in user
   *
   * @return array response content
   */
  public function getMyId() {
    $response = $this->getMe($format='simplexml');
    return (int) $response['body']->id;
  }
  
  /**
   * get all people
   *
   */  
  public function getPeople() {
    return $this->processRequest("{$this->baseurl}people.xml","GET");
  }  

  /**
   * get all people for a project
   *
   * @param int $project_id
   * @return array response content
   */  
  public function getPeopleForProject($project_id,$format=null) {
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
    return $this->processRequest("{$this->baseurl}projects/{$project_id}/people.xml","GET",$format);
  }  

  /**
   * get array of ids for all people in a project
   *
   * @param int $project_id
   * @return array ids of people
   */  
  public function getPeopleIdsForProject($project_id) {
    $response = $this->getPeopleForProject($project_id,'simplexml');
    
    $ids = array();
    foreach($response['body']->person as $person) {
      $ids[] = (int)$person->id;
    }
    
    return $ids;
  }  
  
  /**
   * get all people for a company
   *
   * @param int $company_id
   * @param string $format format of response (opt)
   * @return array response content
   */  
  public function getPeopleForCompany($company_id,$format=null) {
    if(!preg_match('!^\d+$!',$company_id))
      throw new InvalidArgumentException("company id must be a number.");
    return $this->processRequest("{$this->baseurl}companies/{$company_id}/people.xml","GET",$format);
  }  

  /**
   * get array of ids for all people in a company
   *
   * @param int $company_id
   * @return array ids of people
   */  
  public function getPeopleIdsForCompany($company_id) {
    $response = $this->getPeopleForCompany($company_id,'simplexml');
    
    $ids = array();
    foreach($response['body']->person as $person) {
      $ids[] = (int)$person->id;
    }
    
    return $ids;
  }  
  
  /**
   * get a person
   *
   * @param int $person_id
   * @return array response content
   */  
  public function getPerson($person_id) {
    if(!preg_match('!^\d+$!',$person_id))
      throw new InvalidArgumentException("person id must be a number.");
    return $this->processRequest("{$this->baseurl}people/{$person_id}.xml","GET");
  }
  
  /**
   * gets all companies
   *
   * @return array response content
   */  
  public function getCompanies() {
    return $this->processRequest("{$this->baseurl}companies.xml","GET");
  }
  
  /**
   * get all companies for a project
   *
   * @param int $project_id
   * @return array response content
   */  
  public function getCompaniesForProject($project_id,$format=null) {
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
    return $this->processRequest("{$this->baseurl}projects/{$project_id}/companies.xml","GET",$format);
  }  

  /**
   * get array of ids for all companies in a project
   *
   * @param int $project_id
   * @param string $format format of response (opt)
   * @return array response content
   */  
  public function getCompanyIdsForProject($project_id) {
    $response = $this->getCompaniesForProject($project_id,'simplexml');
    
    $ids = array();
    foreach($response['body']->company as $company) {
      $ids[] = (int)$company->id;
    }
    
    return $ids;
  }
  
  /**
   * get a single company
   *
   * @param int $company_id
   * @return array response content
   */  
  public function getCompany($company_id) {
    if(!preg_match('!^\d+$!',$company_id))
      throw new InvalidArgumentException("company id must be a number.");
    return $this->processRequest("{$this->baseurl}companies/{$company_id}.xml","GET");
  } 
  
  /**
   * get all categories for a project
   *
   * @param int $project_id
   * @param string $type the category type
   * possible values:
   * <ul>
   *   <li>post</li>
   *   <li>attachment</li>
   * </ul>
   * @param string $format format of response (opt)
   * @return array response content
   */  
  public function getCategoriesForProject($project_id,$type='post',$format=null) {
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
  	$type = strtolower($type);
    if(!in_array($type,array('post','attachment')))
      throw new InvalidArgumentException("'{$type}' is an invalid category type.");
    return $this->processRequest("{$this->baseurl}projects/{$project_id}/categories.xml?type={$type}","GET",$format);
  }  
  
  /**
   * get array of ids for all categories for a project
   *
   * @param int $project_id
   * @return array response content
   */  
  public function getCategoryIdsForProject($project_id,$type='post') {
    $response = $this->getCategoriesForProject($project_id,$type,'simplexml');
    
    $ids = array();
    foreach($response['body']->category as $category) {
      $ids[] = (int)$category->id;
    }
    
    return $ids;
  }  
  
  /**
   * get a single category
   *
   * @param int $category_id
   * @return array response content
   */  
  public function getCategory($category_id) {
    if(!preg_match('!^\d+$!',$category_id))
      throw new InvalidArgumentException("category id must be a number.");
    return $this->processRequest("{$this->baseurl}categories/{$category_id}.xml","GET");
  } 
  
  /**
   * create a category for a project
   *
   * @param int $project_id
   * @param string $category_name the new category name
   * @param string $type the category type
   * possible values:
   * <ul>
   *   <li>post</li>
   *   <li>attachment</li>
   * </ul>
   * @return array response content
   */  
  public function createCategoryForProject($project_id,$category_name,$type='post') {
  	$type = strtolower($type);
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
    if(empty($category_name))
      throw new InvalidArgumentException("category name cannot be empty.");
  	$type = strtolower($type);
    if(!in_array($type,array('post','attachment')))
      throw new InvalidArgumentException("'{$type}' is an invalid category type.");
    
    $body = array(
              'category'=>array(
                'type'=>$type,
                'name'=>$category_name
                )
            );
    
    $this->setupRequestBody($body);
    $response = $this->processRequest("{$this->baseurl}projects/{$project_id}/categories.xml","POST");
    // set new category id
    if(preg_match('!(\d+)\.xml!',$response['location'],$match))
      $response['id'] = $match[1];
    else
      $response['id'] = null;
    return $response;
  }
  
  /**
   * update a category name
   *
   * @param int $category_id
   * @param string $category_name the new category name
   * @return array response content
   */  
  public function updateCategoryName($category_id,$category_name) {
    if(!preg_match('!^\d+$!',$category_id))
      throw new InvalidArgumentException("category id must be a number.");
    if(empty($category_name))
      throw new InvalidArgumentException("'{$category_name}' cannot be empty.");
    
    $body = array(
              'category'=>array(
                'name'=>$category_name
                )
            );
    
    $this->setupRequestBody($body);
    return $this->processRequest("{$this->baseurl}categories/{$category_id}.xml","PUT");
  } 

  /**
   * delete a category
   *
   * @param int $category_id
   * @return array response content
   */  
  public function deleteCategory($category_id) {
    if(!preg_match('!^\d+$!',$category_id))
      throw new InvalidArgumentException("category id must be a number.");
    return $this->processRequest("{$this->baseurl}categories/{$category_id}.xml","DELETE");
  } 

  /**
   * get messages for a project (basecamp api returns last 25)
   *
   * @param int $project_id
   * @return array response content
   */  
  public function getMessagesForProject($project_id) {
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
    return $this->processRequest("{$this->baseurl}projects/{$project_id}/posts.xml","GET");
  }  
  
  /**
   * get a single message
   *
   * @param int $message_id
   * @return array response content
   */  
  public function getMessage($message_id) {
    if(!preg_match('!^\d+$!',$message_id))
      throw new InvalidArgumentException("message id must be a number.");
    return $this->processRequest("{$this->baseurl}posts/{$message_id}.xml","GET");
  } 
  
  /**
   * get last messages for a project within a specific category
   *
   * @param int $project_id
   * @param int $category_id
   * @return array response content
   */  
  public function getMessagesForProjectForCategory($project_id,$category_id) {
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
    if(!preg_match('!^\d+$!',$category_id))
      throw new InvalidArgumentException("category id must be a number.");
    return $this->processRequest("{$this->baseurl}projects/{$project_id}/cat/{$category_id}/posts.xml","GET");
  }  
  
  /**
   * get archived message summary for project
   *
   * @param int $project_id
   * @return array response content
   */  
  public function getArchivedMessageSummaryForProject($project_id) {
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
    return $this->processRequest("{$this->baseurl}projects/{$project_id}/posts/archive.xml","GET");
  }  
  
  /**
   * get archived message summary for a project within a specific category
   *
   * @param int $project_id
   * @param int $category_id
   * @return array response content
   */  
  public function getArchivedMessageSummaryForProjectForCategory($project_id,$category_id) {
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
    if(!preg_match('!^\d+$!',$category_id))
      throw new InvalidArgumentException("category id must be a number.");
    return $this->processRequest("{$this->baseurl}projects/{$project_id}/cat/{$category_id}/posts/archive.xml","GET");
  }  
  
  /**
   * returns blank XML template for a new message for a project
   *
   * @param int $project_id
   * @return array response content
   */  
  public function newMessageTemplateForProject($project_id) {
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
    return $this->processRequest("{$this->baseurl}projects/{$project_id}/posts/new.xml","GET");
  }  
  
  /**
   * creates a new message for a project
   *
   * @param int $project_id
   * @param string $title title of message
   * @param string $body the body of the message (opt)
   * @param int $category_id category id of message (opt)
   * @param array $notify_people array of people ids to notify (opt)
   * @param array $attachments array of file arrays (file-id,mime-type,file-name) (opt)
   * @param string $extended_body entended body of message
   * @param int $milestone_id milestone id of message (opt)
   * @param bool $private set if message is private (opt, default false)
   * @return array response content
   */  
  public function createMessageForProject(
    $project_id,
    $title,
    $body=null,
    $category_id=null,
    $extended_body=null,
    $milestone_id=0,
    $private=false,
    $notify_people=array(),
    $attachments=array()
    ) {
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
    if(empty($title))
      throw new InvalidArgumentException("title cannot be empty.");
    if(!is_array($notify_people))
      throw new InvalidArgumentException("notify people must be an array.");
    if(!is_array($attachments))
      throw new InvalidArgumentException("attachments must be an array.");
    
    $body = array(
              'post'=>array(
                'category-id'=>$category_id,
                'title'=>$title,
                'body'=>$body,
                'extended-body'=>$extended_body,
                'private'=>$private
                )
            );
    
    if(!empty($notify_people)) {
      foreach($notify_people as $key=>$val)
        // hack to take multiple same-named keys :)
        $body['notify:'.$key] = $val;
    }
    
    if(!empty($attachments)) {
      foreach($attachments as $key=>$attachment)
        $attach_info = array(
          'name' => $attachment[0],
          'file' => array(
            'file' => $attachment[1],
            'content-type' => $attachment[2],
            'original-filename' => $attachment[3]
            )
        );
        $body['attachments:'.$key] = $attach_info;
    }
    
    $this->setupRequestBody($body);
    
    $response = $this->processRequest("{$this->baseurl}projects/{$project_id}/posts.xml","POST");
    // set new message id
    if(preg_match('!(\d+)\.xml!',$response['location'],$match))
      $response['id'] = $match[1];
    else
      $response['id'] = null;
    return $response;    
  }
  
  /**
   * upload a file to basecamp
   *
   * @param string $filepath
   * @return string basecamp file id of uploaded file
   */  
  public function uploadFile($filepath) {
    if(empty($filepath))
      throw new InvalidArgumentException("filepath cannot be empty.");
    if(!file_exists($filepath))
      throw new InvalidArgumentException("'{$filepath}' does not exist.");
    if(!is_readable($filepath))
      throw new InvalidArgumentException("'{$filepath}' is not readable.");
    $this->setRequestBody(file_get_contents($filepath));
    
    $response = $this->processRequest("{$this->baseurl}upload","POST","simplexml");
    
    if(!empty($response['body']->id))
      return (string)$response['body']->id;
    else
      return false;
  }  
  
  /**
   * returns XML for existing message (for editing purposes)
   *
   * @param int $message_id
   * @return mixed message content
   */  
  public function editMessage($message_id) {
    if(!preg_match('!^\d+$!',$message_id))
      throw new InvalidArgumentException("message id must be a number.");
    return $this->processRequest("{$this->baseurl}posts/{$project_id}.xml","GET");
  }  
  
  /**
   * update a message
   *
   * @param int $message_id
   * @param string $category_name the new category name
   * @return array response content
   */  
  public function updateMessage(
    $message_id,
    $title,
    $body=null,
    $category_id=null,
    $extended_body=null,
    $milestone_id=0,
    $private=false,
    $notify_people=array(),
    $attachments=array()
    ) {
    if(!preg_match('!^\d+$!',$message_id))
      throw new InvalidArgumentException("message id must be a number.");
    if(empty($title))
      throw new InvalidArgumentException("category name cannot be empty.");
    
    $body = array(
              'post'=>array(
                'category-id'=>$category_id,
                'title'=>$title,
                'body'=>$body,
                'extended-body'=>$extended_body,
                'private'=>$private
                )
            );
    
    if(!empty($notify_people)) {
      foreach($notify_people as $key=>$val)
        // hack to take multiple same-named keys :)
        $body['notify:'.$key] = $val;
    }
    
    if(!empty($attachments)) {
      foreach($attachments as $key=>$attachment)
        $attach_info = array(
          'name' => $attachment[0],
          'file' => array(
            'file' => $attachment[1],
            'content-type' => $attachment[2],
            'original-filename' => $attachment[3]
            )
        );
        $body['attachments:'.$key] = $attach_info;
    }
    
    $this->setupRequestBody($body);
    
    return $this->processRequest("{$this->baseurl}posts/{$message_id}.xml","PUT");
  } 

  /**
   * delete a message
   *
   * @param int $message_id
   * @return array response content
   */  
  public function deleteMessage($message_id) {
    if(!preg_match('!^\d+$!',$message_id))
      throw new InvalidArgumentException("message id must be a number.");
    return $this->processRequest("{$this->baseurl}posts/{$message_id}.xml","DELETE");
  } 

  /**
   * get recent comments for a resource (basecamp api returns last 50)
   *
   * @param string $resource_type
   * possible values:
   * <ul>
   *   <li>posts</li>
   *   <li>milestones</li>
   *   <li>todo_items</li>
   * </ul>
   * @param int $resource_id
   * @return array response content
   */  
  public function getRecentCommentsForResource($resource_type,$resource_id) {
  	$resource_type = strtolower($resource_type);
    if(!in_array($resource_type,array('posts','milestones','todo_items')))
      throw new InvalidArgumentException("'{$resource_type}' is an invalid resource type.");
    if(!preg_match('!^\d+$!',$resource_id))
      throw new InvalidArgumentException("resource id must be a number.");
    return $this->processRequest("{$this->baseurl}{$resource_type}/{$resource_id}/comments.xml","GET");
  }

  /**
   * get recent comments for a message (basecamp api returns last 50)
   *
   * @param int $message_id
   * @return array response content
   */  
  public function getRecentCommentsForMessage($message_id) {
    if(!preg_match('!^\d+$!',$message_id))
      throw new InvalidArgumentException("message id must be a number.");
    return $this->getRecentCommentsForResource('posts',$message_id);
  }

  /**
   * get recent comments for a milestone (basecamp api returns last 50)
   *
   * @param int $milestone_id
   * @return array response content
   */  
  public function getRecentCommentsForMilestone($milestone_id) {
    if(!preg_match('!^\d+$!',$milestone_id))
      throw new InvalidArgumentException("milestone id must be a number.");
    return $this->getRecentCommentsForResource('milestones',$milestone_id);
  }

  /**
   * get recent comments for a todo item (basecamp api returns last 50)
   *
   * @param int $todo_id
   * @return array response content
   */  
  public function getRecentCommentsForTodoItem($todo_id) {
    if(!preg_match('!^\d+$!',$todo_id))
      throw new InvalidArgumentException("todo id must be a number.");
    return $this->getRecentCommentsForResource('todo_items',$todo_id);
  }
  
  /**
   * get a single comment
   *
   * @param int $comment_id
   * @return array response content
   */  
  public function getComment($comment_id) {
    if(!preg_match('!^\d+$!',$comment_id))
      throw new InvalidArgumentException("comment_id id must be a number.");
    return $this->processRequest("{$this->baseurl}comments/{$comment_id}.xml","GET");
  } 
  
  /**
   * returns blank XML template for a new comment for a resource
   *
   * @param string $resource_type
   * possible values:
   * <ul>
   *   <li>posts</li>
   *   <li>milestones</li>
   *   <li>todo_items</li>
   * </ul>
   * @param int $project_id
   * @return array response content
   */  
  public function newCommentTemplateForResource($resource_type,$resource_id) {
  	$resource_type = strtolower($resource_type);
    if(!in_array($resource_type,array('posts','milestones','todo_items')))
      throw new InvalidArgumentException("'{$resource_type}' is an invalid resource type.");
    if(!preg_match('!^\d+$!',$resource_id))
      throw new InvalidArgumentException("resource id must be a number.");
    return $this->processRequest("{$this->baseurl}{$resource_type}/{$resource_id}/comments/new.xml","GET");
  }  
  
  /**
   * returns blank XML template for a new comment for a message
   *
   * @param int $message_id
   * @return array response content
   */  
  public function newCommentTemplateForMessage($message_id) {
    return $this->newCommentTemplateForResource('posts',$message_id);
  }

  /**
   * returns blank XML template for a new comment for a milestone
   *
   * @param int $milestone_id
   * @return array response content
   */  
  public function newCommentTemplateForMilestone($milestone_id) {
    return $this->newCommentTemplateForResource('milestones',$milestone_id);
  }
  
  /**
   * returns blank XML template for a new comment for a todo item
   *
   * @param int $todo_id
   * @return array response content
   */  
  public function newCommentTemplateForTodoItem($todo_id) {
    return $this->newCommentTemplateForResource('todo_items',$todo_id);
  }
  
  /**
   * creates a new comment for a resource
   *
   * @param string $resource_type
   * @param int $resource_id
   * @param string $body the body of the comment
   * @return array response content
   */  
  public function createCommentForResource(
    $resource_type,
    $resource_id,
    $body
    ) {
    if(empty($resource_type))
      throw new InvalidArgumentException("resource type cannot be empty.");
    if(!preg_match('!^\d+$!',$resource_id))
      throw new InvalidArgumentException("resource id must be a number.");
    if(empty($body))
      throw new InvalidArgumentException("comment body cannot be empty.");
    
    $body = array(
              'comment'=>array(
                'body'=>$body
                )
            );
    
    $this->setupRequestBody($body);
    
    $response = $this->processRequest("{$this->baseurl}{$resource_type}/{$resource_id}/comments.xml","POST");
    // set new comment id
    if(preg_match('!(\d+)\.xml!',$response['location'],$match))
      $response['id'] = $match[1];
    else
      $response['id'] = null;
    return $response;    
  }
  
  /**
   * creates a new comment for a message
   *
   * @param int $message_id
   * @param string $body the body of the comment
   * @return array response content
   */  
  public function createCommentForMessage(
    $message_id,
    $body
    ) {
    return $this->createCommentForResource('posts',$message_id,$body);
  }
  
  /**
   * creates a new comment for a milestone
   *
   * @param int $message_id
   * @param string $body the body of the comment
   * @return array response content
   */  
  public function createCommentForMilestone(
    $message_id,
    $body
    ) {
    return $this->createCommentForResource('milestone',$message_id,$body);
  }
  
  /**
   * creates a new comment for a todo item
   *
   * @param int $message_id
   * @param string $body the body of the comment
   * @return array response content
   */  
  public function createCommentForTodoItem(
    $message_id,
    $body
    ) {
    return $this->createCommentForResource('todo_items',$message_id,$body);
  }  
  
  /**
   * returns XML for existing comment (for editing purposes)
   *
   * @param int $comment_id
   * @return mixed response content
   */  
  public function editComment($comment_id) {
    if(!preg_match('!^\d+$!',$comment_id))
      throw new InvalidArgumentException("comment id must be a number.");
    return $this->processRequest("{$this->baseurl}comments/{$comment_id}.xml","GET");
  }  
  
  /**
   * updates a comment
   *
   * @param int $comment_id
   * @param string $body the body of the comment
   * @return array response content
   */  
  public function updateComment(
    $comment_id,
    $body
    ) {
    if(!preg_match('!^\d+$!',$comment_id))
      throw new InvalidArgumentException("comment id must be a number.");
    if(empty($body))
      throw new InvalidArgumentException("comment body cannot be empty.");
    
    $content = array(
              'comment'=>array(
                'body'=>$body
                )
            );
    
    $this->setupRequestBody($content);
    
    return $this->processRequest("{$this->baseurl}comments/{$comment_id}.xml","PUT");
  }
  
  /**
   * deletes a comment
   *
   * @param int $comment_id
   * @return array response content
   */  
  public function deleteComment($comment_id) {
    if(!preg_match('!^\d+$!',$comment_id))
      throw new InvalidArgumentException("comment id must be a number.");
    return $this->processRequest("{$this->baseurl}comments/{$comment_id}.xml","DELETE");
  }

  /**
   * get todo lists (and todo list items) for a resource
   *
   * @param string $resource_type
   * possible values:
   * <ul>
   *   <li>unassigned</li>
   *   <li>person</li>
   *   <li>company</li>
   * </ul>
   * @param int $resource_id
   * @return array response content
   */  
  public function getTodoListsForResource($resource_type,$resource_id=null) {
  	$resource_type = strtolower($resource_type);
    if(!in_array($resource_type,array('me','person','company')))
      throw new InvalidArgumentException("'{$resource_type}' is an invalid resource type.");
    if($resource_type !== 'me' && !preg_match('!^\d+$!',$resource_id))
      throw new InvalidArgumentException("resource id must be a number.");
    
    if($resource_type == 'unassigned')
      $responsible_party = '';
    elseif($resource_type == 'person')
      $responsible_party = $resource_id;      
    elseif($resource_type == 'company')
      $responsible_party = "c{$resource_id}";
    
    return $this->processRequest("{$this->baseurl}todo_lists.xml?responsible_party={$responsible_party}","GET");
  }
  
  /**
   * get unassigned todo lists
   *
   * @return array response content
   */  
  public function getTodoListsForUnassigned() {
    return $this->getTodoListsForResource('unassigned');
  }  

  /**
   * get todo lists (and items) for a person
   *
   * @return array response content
   */  
  public function getTodoListsForPerson($person_id) {
    return $this->getTodoListsForResource('person',$person_id);
  }  

  /**
   * get todo lists (and items) for a company
   *
   * @return array response content
   */  
  public function getTodoListsForCompany($company_id) {
    if(!preg_match('!^\d+$!',$company_id))
      throw new InvalidArgumentException("company id must be a number.");
    return $this->getTodoListsForResource('company',$company_id);
  }  

  /**
   * get todo lists (and todo list items) for a project
   *
   * @param int $project_id
   * @param string $filter_type
   * possible values:
   * <ul>
   *   <li>all</li>
   *   <li>pending</li>
   *   <li>finished</li>
   * </ul>
   * @return array response content
   */  
  public function getTodoListsForProject($project_id,$filter_type='all',$format=null) {
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
  	$filter_type = strtolower($filter_type);
    if(!in_array($filter_type,array('all','pending','finished')))
      throw new InvalidArgumentException("'{$filter_type}' is an invalid filter type.");
    
    return $this->processRequest("{$this->baseurl}projects/{$project_id}/todo_lists.xml?filter={$filter_type}","GET",$format);
  }
  
  /**
   * get array of ids for all todo lists in a project
   *
   * @param int $project_id
   * @param string $filter_type
   * possible values:
   * <ul>
   *   <li>all</li>
   *   <li>pending</li>
   *   <li>finished</li>
   * </ul>
   * @param string $format format of response (opt)
   * @return array response content
   */  
  public function getTodoListIdsForProject($project_id,$filter_type='all') {
    $response = $this->getTodoListsForProject($project_id,$filter_type,'simplexml');
    
    $ids = array();
    foreach($response['body']->{'todo-list'} as $list) {
      $ids[] = (int)$list->id;
    }
    
    return $ids;
  }
  
  /**
   * get a todo list
   *
   * @param int $todo_list_id
   * @return array response content
   */  
  public function getTodoList($todo_list_id) {
    if(!preg_match('!^\d+$!',$todo_list_id))
      throw new InvalidArgumentException("todo list id must be a number.");
    return $this->processRequest("{$this->baseurl}todo_lists/{$todo_list_id}.xml","GET");
  }  
  
  /**
   * returns XML for existing todo list (for editing purposes)
   *
   * @param int $todo_list_id
   * @return mixed response content
   */  
  public function editTodoList($todo_list_id) {
    if(!preg_match('!^\d+$!',$todo_list_id))
      throw new InvalidArgumentException("todo list id must be a number.");
    return $this->processRequest("{$this->baseurl}todo_lists/{$todo_list_id}/edit.xml","GET");
  }  
  
  /**
   * updates a todo list
   *
   * @param int $todo_list_id
   * @param string $name list name
   * @param string $description
   * @param int $milestone_id milestone (0 for none)
   * @param bool $private list private?
   * @param bool $tracked list time tracked?
   * @return array response content
   */  
  public function updateTodoList(
    $todo_list_id,
    $name,
    $description=null,
    $milestone_id=null,
    $private=null,
    $tracked=null
    ) {
    if(!preg_match('!^\d+$!',$todo_list_id))
      throw new InvalidArgumentException("todo list id must be a number.");
    if(empty($name))
      throw new InvalidArgumentException("todo list name cannot be empty.");
    
    $content = array(
              'todo-list'=>array(
                'name'=>$name,
                'description'=>$description,
                'milestone-id'=>$milestone_id,
                'private'=>$private,
                'tracked'=>$tracked
                )
            );
    
    $this->setupRequestBody($content);
    
    return $this->processRequest("{$this->baseurl}todo_lists/{$todo_list_id}.xml","PUT");
  }
  
  /**
   * returns blank XML template for a new todo list for a project
   *
   * @param int $project_id
   * @return array response content
   */  
  public function newTodoListTemplateForProject($project_id) {
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
    return $this->processRequest("{$this->baseurl}projects/{$project_id}/todo_lists/new.xml","GET");
  }
  
  /**
   * creates a todo list
   *
   * @param int $project_id
   * @param string $name list name
   * @param string $description
   * @param int $milestone_id milestone (0 for none)
   * @param bool $private list private?
   * @param bool $tracked list time tracked?
   * @return array response content
   */  
  public function createTodoListForProject(
    $project_id,
    $name,
    $description=null,
    $milestone_id=null,
    $private=null,
    $tracked=null
    ) {
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
    if(empty($name))
      throw new InvalidArgumentException("todo list name cannot be empty.");
    
    $content = array(
              'todo-list'=>array(
                'name'=>$name,
                'description'=>$description,
                'milestone-id'=>$milestone_id,
                'private'=>$private,
                'tracked'=>$tracked
                )
            );
    
    $this->setupRequestBody($content);
    
    $response = $this->processRequest("{$this->baseurl}projects/{$project_id}/todo_lists.xml","POST");
    // set new list id
    if(preg_match('!(\d+)$!',$response['location'],$match))
      $response['id'] = $match[1];
    else
      $response['id'] = null;
    return $response;    
  }
  
  /**
   * deletes a todo list
   *
   * @param int $todo_list_id
   * @return array response content
   */  
  public function deleteTodoList($todo_list_id) {
    if(!preg_match('!^\d+$!',$todo_list_id))
      throw new InvalidArgumentException("todo list id must be a number.");
    return $this->processRequest("{$this->baseurl}todo_lists/{$todo_list_id}.xml","DELETE");
  }

  /**
   * re-orders todo lists for a project
   *
   * @param int $project_id
   * @param array $list_ids array of list ids sorted in new order
   * @return array response content
   */  
  public function reorderTodoListsForProject($project_id,$list_ids) {
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
    
    $content = array(
              'todo-lists type="array"'=>array(
                )
            );
    foreach($list_ids as $key=>$list_id)
      $content['todo-lists type="array"']['todo-list:'.$key] = array('id'=>$list_id);
    
    $this->setupRequestBody($content);
    
    return $this->processRequest("{$this->baseurl}projects/{$project_id}/todo_lists/reorder.xml","POST");
  }

  /**
   * get all todo items for a list
   *
   * @param int $todo_list_id
   * @param string $format format of response (opt)
   * @return array response content
   */  
  public function getTodoItemsForList($todo_list_id,$format=null) {
    if(!preg_match('!^\d+$!',$todo_list_id))
      throw new InvalidArgumentException("todo list id must be a number.");
    return $this->processRequest("{$this->baseurl}todo_lists/{$todo_list_id}/todo_items.xml","GET",$format);
  }  
  
  /**
   * get array of ids for all todo items in a todo list
   *
   * @param int $todo_list_id
   * @return array response content
   */  
  public function getTodoItemIdsForList($todo_list_id) {
    $response = $this->getTodoItemsForList($todo_list_id,'simplexml');
    
    $ids = array();
    foreach($response['body']->{'todo-item'} as $item) {
      $ids[] = (int)$item->id;
    }
    
    return $ids;
  }

  /**
   * get a single todo item
   *
   * @param int $item_id
   * @return array response content
   */  
  public function getTodoItem($item_id) {
    if(!preg_match('!^\d+$!',$item_id))
      throw new InvalidArgumentException("item id must be a number.");
    return $this->processRequest("{$this->baseurl}todo_items/{$item_id}.xml","GET");
  } 

  /**
   * complete a todo item
   *
   * @param int $item_id
   * @return array response content
   */  
  public function completeTodoItem($item_id) {
    if(!preg_match('!^\d+$!',$item_id))
      throw new InvalidArgumentException("item id must be a number.");
    return $this->processRequest("{$this->baseurl}todo_items/{$item_id}/complete.xml","PUT");
  } 
  
  /**
   * uncomplete a todo item
   *
   * @param int $item_id
   * @return array response content
   */  
  public function uncompleteTodoItem($item_id) {
    if(!preg_match('!^\d+$!',$item_id))
      throw new InvalidArgumentException("item id must be a number.");
    return $this->processRequest("{$this->baseurl}todo_items/{$item_id}/uncomplete.xml","PUT");
  } 

  /**
   * returns blank XML template for a new todo item for a todo list
   *
   * @param int $todo_list_id
   * @return array response content
   */  
  public function newTodoItemTemplateForList($todo_list_id) {
    if(!preg_match('!^\d+$!',$todo_list_id))
      throw new InvalidArgumentException("todo list id must be a number.");
    return $this->processRequest("{$this->baseurl}todo_lists/{$todo_list_id}/todo_items/new.xml","GET");
  }
  
  /**
   * creates a todo item
   *
   * @param int $todo_list_id
   * @param string $content todo item content
   * @param string $responsible_party_type
   * possible values:
   * <ul>
   *   <li>person</li>
   *   <li>company</li>
   * </ul>
   * @param int $responsible_party_id
   * @param bool $notify send notifications?
   * @return array response content
   */  
  public function createTodoItemForList(
    $todo_list_id,
    $content,
    $responsible_party_type=null,
    $responsible_party_id=null,
    $notify=null
    ) {
    if(!preg_match('!^\d+$!',$todo_list_id))
      throw new InvalidArgumentException("todo list id must be a number.");
    if(empty($content))
      throw new InvalidArgumentException("todo item content cannot be empty.");
  	$responsible_party_type = strtolower($responsible_party_type);
    if(isset($responsible_party_type) && !in_array($responsible_party_type,array('person','company')))
      throw new InvalidArgumentException("'{$responsible_party_type}' is not a valid responsible party type.");
    if(!empty($responsible_party_type) && empty($responsible_party_id))
      throw new InvalidArgumentException("responsible party id cannot be empty.");
    
    if($responsible_party_type == 'person')
      $resp_party = $responsible_party_id;
    elseif($responsible_party_type == 'company')
      $resp_party = "c{$responsible_party_id}";
    else
      $resp_party = '';      
      
    $data = array(
              'todo-item'=>array(
                'content'=>$content,
                'responsible-party'=>$resp_party,
                'notify type="boolean"'=>$notify
                )
            );
    
    $this->setupRequestBody($data);
    
    $response = $this->processRequest("{$this->baseurl}todo_lists/{$todo_list_id}/todo_items.xml","POST");
    // set new list id
    if(preg_match('!(\d+)$!',$response['location'],$match))
      $response['id'] = $match[1];
    else
      $response['id'] = null;
    return $response;    
  }
  
  /**
   * updates a todo item
   *
   * @param int $todo_id
   * @param string $content todo item content
   * @param string $responsible_party_type
   * possible values:
   * <ul>
   *   <li>person</li>
   *   <li>company</li>
   * </ul>
   * @param int $responsible_party_id
   * @param bool $notify send notifications?
   * @return array response content
   */  
  public function updateTodoItem(
    $todo_id,
    $content,
    $responsible_party_type=null,
    $responsible_party_id=null,
    $notify=null
    ) {
    if(!preg_match('!^\d+$!',$todo_id))
      throw new InvalidArgumentException("todo id must be a number.");
    if(empty($content))
      throw new InvalidArgumentException("todo item content cannot be empty.");
  	$responsible_party_type = strtolower($responsible_party_type);
    if(isset($responsible_party_type) && !in_array($responsible_party_type,array('person','company')))
      throw new InvalidArgumentException("'{$responsible_party_type}' is not a valid responsible party type.");
    if(!empty($responsible_party_type) && empty($responsible_party_id))
      throw new InvalidArgumentException("responsible party id cannot be empty.");
    
    if($responsible_party_type == 'person')
      $resp_party = $responsible_party_id;
    elseif($responsible_party_type == 'company')
      $resp_party = "c{$responsible_party_id}";
    else
      $resp_party = '';      
      
    $data = array(
              'todo-item'=>array(
                'content'=>$content,
                'responsible-party'=>$resp_party,
                'notify type="boolean"'=>$notify
                )
            );
    
    $this->setupRequestBody($data);
    
    return $this->processRequest("{$this->baseurl}todo_items/{$todo_id}.xml","PUT");
  }
  
  /**
   * returns XML for existing todo list (for editing purposes)
   *
   * @param int $todo_id
   * @return mixed response content
   */  
  public function editTodoItem($todo_id) {
    if(!preg_match('!^\d+$!',$todo_id))
      throw new InvalidArgumentException("todo id must be a number.");
    return $this->processRequest("{$this->baseurl}todo_items/{$todo_id}/edit.xml","GET");
  }  
  
  /**
   * deletes a todo item
   *
   * @param int $todo_id
   * @return array response content
   */  
  public function deleteTodoItem($todo_id) {
    if(!preg_match('!^\d+$!',$todo_id))
      throw new InvalidArgumentException("todo id must be a number.");
    return $this->processRequest("{$this->baseurl}todo_items/{$todo_id}.xml","DELETE");
  }
  
  /**
   * re-orders todo itesm for a list
   *
   * @param int $todo_list_id
   * @param array $todo_ids array of todo item ids sorted in new order
   * @return array response content
   */  
  public function reorderTodoItemsForList($todo_list_id,$todo_ids=array()) {
    if(!preg_match('!^\d+$!',$todo_list_id))
      throw new InvalidArgumentException("todo list id must be a number.");
    if(!is_array($todo_ids))
      throw new InvalidArgumentException("todo item ids must be an array.");
    
    $content = array(
              'todo-items type="array"'=>array(
                )
            );
    foreach($todo_ids as $key=>$todo_id)
      $content['todo-items type="array"']['todo-item:'.$key] = array('id'=>$todo_id);
    
    $this->setupRequestBody($content);
    
    return $this->processRequest("{$this->baseurl}todo_lists/{$todo_list_id}/todo_items/reorder.xml","POST");
  }
  
  /**
   * get milestones for a project
   *
   * @param int $project_id
   * @param string $filter_type
   * possible values:
   * <ul>
   *   <li>all</li>
   *   <li>late</li>
   *   <li>completed</li>
   *   <li>upcoming</li>
   * </ul>
   * @return array response content
   */  
  public function getMilestonesForProject($project_id,$filter_type='all',$format=null) {
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
  	$filter_type = strtolower($filter_type);
    if(!in_array($filter_type,array('all','late','completed','upcoming')))
      throw new InvalidArgumentException("'{$filter_type}' is not a valid filter type.");
    
    $data = array(
                'find'=>$filter_type
            );
    
    $this->setupRequestBody($data);    
    
    return $this->processRequest("{$this->baseurl}projects/{$project_id}/milestones/list","POST",$format);
  }

  /**
   * complete a milestone
   *
   * @param int $milestone_id
   * @return array response content
   */  
  public function completeMilestone($milestone_id) {
    if(!preg_match('!^\d+$!',$milestone_id))
      throw new InvalidArgumentException("milestone id must be a number.");
    return $this->processRequest("{$this->baseurl}/milestones/complete/{$milestone_id}","PUT");
  }

  /**
   * uncomplete a milestone
   *
   * @param int $milestone_id
   * @return array response content
   */  
  public function uncompleteMilestone($milestone_id) {
    if(!preg_match('!^\d+$!',$milestone_id))
      throw new InvalidArgumentException("milestone id must be a number.");
    return $this->processRequest("{$this->baseurl}/milestones/uncomplete/{$milestone_id}","PUT");
  }

  /**
   * create a milestone for a project
   *
   * @param int $project_id
   * @param string $title the new milestone title
   * @param string $deadline date in format YYYY-MM-DD
   * @param string $responsible_party_type
   * possible values:
   * <ul>
   *   <li>person</li>
   *   <li>company</li>
   * </ul>
   * @param int $responsible_party_id
   * @param bool $notify send notifications?
   * @return array response content
   */  
  public function createMilestoneForProject(
    $project_id,
    $title,
    $deadline,
    $responsible_party_type=null,
    $responsible_party_id=null,
    $notify=null) {
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
    if(empty($title))
      throw new InvalidArgumentException("title cannot be empty.");
    if(empty($deadline))
      throw new InvalidArgumentException("deadline date cannot be empty.");
  	$responsible_party_type = strtolower($responsible_party_type);
    if(isset($responsible_party_type) && !in_array($responsible_party_type,array('person','company')))
      throw new InvalidArgumentException("'{$responsible_party_type}' is not a valid responsible party type.");
    if(!empty($responsible_party_type) && empty($responsible_party_id))
      throw new InvalidArgumentException("responsible party id cannot be empty.");
    
    if($responsible_party_type == 'person')
      $resp_party = $responsible_party_id;
    elseif($responsible_party_type == 'company')
      $resp_party = "c{$responsible_party_id}";
    else
      $resp_party = '';      
    
    // if date is not in correct format, try to reformat it
    if(!preg_match('!^\d{4}-\d{2}-\d{2}$!',$deadline))
      $deadline = strftime('%Y-%m-%d',strtotime($deadline));
      
    $body = array(
              'milestone'=>array(
                'title'=>$title,
                'deadline type="date"'=>$deadline,
                'responsible-party'=>$resp_party,
                'notify'=>$notify
                )
            );
    
    $this->setupRequestBody($body);
    
    $response = $this->processRequest("{$this->baseurl}projects/{$project_id}/milestones/create","POST");
    $response['id'] = null;
    // basecamp doesn't supply a location, so fish milestone id out of body :(
    if($this->format == 'xml') {
      if(preg_match('!<id.*>(\d+)</id>!',$response['body'],$match))
        $response['id'] = $match[1];
    } elseif ($this->format == 'simplexml') {
        $response['id'] = (string) $response['body']->milestone->id[0];
    }
    
    return $response;
    
  }
  
  /**
   * update a milestone
   *
   * @param int $milestone_id
   * @param string $title the new milestone title
   * @param string $deadline date in format YYYY-MM-DD
   * @param string $responsible_party_type
   * possible values:
   * <ul>
   *   <li>person</li>
   *   <li>company</li>
   * </ul>
   * @param int $responsible_party_id
   * @param bool $notify send notifications?
   * @return array response content
   */  
  public function updateMilestone(
    $milestone_id,
    $title,
    $deadline,
    $responsible_party_type=null,
    $responsible_party_id=null,
    $notify=null) {
    if(!preg_match('!^\d+$!',$milestone_id))
      throw new InvalidArgumentException("milestone id must be a number.");
    if(empty($title))
      throw new InvalidArgumentException("title cannot be empty.");
    if(empty($deadline))
      throw new InvalidArgumentException("deadline date cannot be empty.");
  	$responsible_party_type = strtolower($responsible_party_type);
    if(isset($responsible_party_type) && !in_array($responsible_party_type,array('person','company')))
      throw new InvalidArgumentException("'{$responsible_party_type}' is not a valid responsible party type.");
    if(!empty($responsible_party_type) && empty($responsible_party_id))
      throw new InvalidArgumentException("responsible party id cannot be empty.");
    
    if($responsible_party_type == 'person')
      $resp_party = $responsible_party_id;
    elseif($responsible_party_type == 'company')
      $resp_party = "c{$responsible_party_id}";
    else
      $resp_party = '';      
    
    // if date is not in correct format, try to reformat it
    if(!preg_match('!^\d{4}-\d{2}-\d{2}$!',$deadline))
      $deadline = strftime('%Y-%m-%d',strtotime($deadline));
      
    $body = array(
              'milestone'=>array(
                'title'=>$title,
                'deadline type="date"'=>$deadline,
                'responsible-party'=>$resp_party,
                'notify'=>$notify
                )
            );
    
    $this->setupRequestBody($body);
    
    return $this->processRequest("{$this->baseurl}milestones/update/{$milestone_id}","POST");
    
  }
  
  /**
   * deletes a milestone
   *
   * @param int $milestone_id
   * @return array response content
   */  
  public function deleteMilestone($milestone_id) {
    if(!preg_match('!^\d+$!',$milestone_id))
      throw new InvalidArgumentException("milestone id must be a number.");
    return $this->processRequest("{$this->baseurl}milestones/delete/{$milestone_id}","DELETE");
  }
  
  /**
   * get time entries for a project
   *
   * @param int $project_id
   * @param int $page
   * @return array response content
   */  
  public function getTimeEntriesForProject($project_id,$page=0) {
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
    $response = $this->processRequest("{$this->baseurl}projects/{$project_id}/time_entries.xml?page={$page}","GET");
    if(preg_match('!X-Records: (\d+)!',$response['headers'],$match))
    $response['records'] = $match[1];
    if(preg_match('!X-Pages: (\d+)!',$response['headers'],$match))
    $response['pages'] = $match[1];
    if(preg_match('!X-Page: (\d+)!',$response['headers'],$match))
    $response['page'] = $match[1];
    
    return $response;
  }
  
  /**
   * get time entries for a todo item
   *
   * @param int $todo_id
   * @param int $page
   * @return array response content
   */  
  public function getTimeEntriesForTodoItem($todo_id,$page=0) {
    if(!preg_match('!^\d+$!',$todo_id))
      throw new InvalidArgumentException("todo id must be a number.");
    $response = $this->processRequest("{$this->baseurl}todo_items/{$todo_id}/time_entries.xml?page={$page}","GET");
    if(preg_match('!X-Records: (\d+)!',$response['headers'],$match))
    $response['records'] = $match[1];
    if(preg_match('!X-Pages: (\d+)!',$response['headers'],$match))
    $response['pages'] = $match[1];
    if(preg_match('!X-Page: (\d+)!',$response['headers'],$match))
    $response['page'] = $match[1];
    
    return $response;
  }

  /**
   * create a time tracking entry for a project
   *
   * @param int $project_id
   * @param int $person_id person time entry is for
   * @param string $date date in format YYYY-MM-DD
   * @param string $hours
   * @param string $description
   * @return array response content
   */  
  public function createTimeEntryForProject(
    $project_id,
    $person_id,
    $date,
    $hours,
    $description=null) {
    if(!preg_match('!^\d+$!',$project_id))
      throw new InvalidArgumentException("project id must be a number.");
    if(!preg_match('!^\d+$!',$person_id))
      throw new InvalidArgumentException("person id must be a number.");
    if(empty($date))
      throw new InvalidArgumentException("date cannot be empty.");
    if(empty($hours))
      throw new InvalidArgumentException("hours cannot be empty.");
    
    // if date is not in correct format, try to reformat it
    if(!preg_match('!^\d{4}-\d{2}-\d{2}$!',$date))
      $date = strftime('%Y-%m-%d',strtotime($date));
      
    $data = array(
              'time-entry'=>array(
                'person-id'=>$person_id,
                'date'=>$date,
                'hours'=>$hours,
                'description'=>$description
                )
            );
    
    $this->setupRequestBody($data);
    
    $response = $this->processRequest("{$this->baseurl}/projects/{$project_id}/time_entries.xml","POST");
    // set new time entry id
    if(preg_match('!(\d+)$!',$response['location'],$match))
      $response['id'] = $match[1];
    else
      $response['id'] = null;
    
    return $response;
    
  }

  /**
   * create a time tracking entry for a project
   *
   * @param int $todo_id
   * @param int $person_id person time entry is for
   * @param string $date date in format YYYY-MM-DD
   * @param string $hours
   * @param string $description
   * @return array response content
   */  
  public function createTimeEntryForTodoItem(
    $todo_id,
    $person_id,
    $date,
    $hours,
    $description=null) {
    if(!preg_match('!^\d+$!',$todo_id))
      throw new InvalidArgumentException("todo_ id must be a number.");
    if(!preg_match('!^\d+$!',$person_id))
      throw new InvalidArgumentException("person id must be a number.");
    if(empty($date))
      throw new InvalidArgumentException("date cannot be empty.");
    if(empty($hours))
      throw new InvalidArgumentException("hours cannot be empty.");
    
    // if date is not in correct format, try to reformat it
    if(!preg_match('!^\d{4}-\d{2}-\d{2}$!',$date))
      $date = strftime('%Y-%m-%d',strtotime($date));
      
    $data = array(
              'time-entry'=>array(
                'person-id'=>$person_id,
                'date'=>$date,
                'hours'=>$hours,
                'description'=>$description
                )
            );
    
    $this->setupRequestBody($data);
    
    $response = $this->processRequest("{$this->baseurl}/todo_items/{$todo_id}/time_entries.xml","POST");
    // set new time entry id
    if(preg_match('!(\d+)$!',$response['location'],$match))
      $response['id'] = $match[1];
    else
      $response['id'] = null;
    
    return $response;
    
  }

  /**
   * get a single time entry
   *
   * @param int $entry_id
   * @return array response content
   */  
  public function getTimeEntry($entry_id) {
    if(!preg_match('!^\d+$!',$entry_id))
      throw new InvalidArgumentException("entry id must be a number.");
    return $this->processRequest("{$this->baseurl}time_entries/{$entry_id}.xml","GET");
  } 
  
  /**
   * returns XML for existing time entry (for editing purposes)
   *
   * @param int $entry_id
   * @return mixed response content
   */  
  public function editTimeEntry($entry_id) {
    if(!preg_match('!^\d+$!',$entry_id))
      throw new InvalidArgumentException("entry id must be a number.");
    return $this->processRequest("{$this->baseurl}time_entries/{$entry_id}/edit.xml","GET");
  }  

  /**
   * edit a time tracking entry
   *
   * @param int $entry_id
   * @param int $person_id person time entry is for
   * @param string $date date in format YYYY-MM-DD
   * @param string $hours
   * @param string $description
   * @return array response content
   */  
  public function updateTimeEntry(
    $entry_id,
    $person_id,
    $date,
    $hours,
    $description=null) {
    if(!preg_match('!^\d+$!',$entry_id))
      throw new InvalidArgumentException("entry id must be a number.");
    if(!preg_match('!^\d+$!',$person_id))
      throw new InvalidArgumentException("person id must be a number.");
    if(empty($date))
      throw new InvalidArgumentException("date cannot be empty.");
    if(empty($hours))
      throw new InvalidArgumentException("hours cannot be empty.");
    
    // if date is not in correct format, try to reformat it
    if(!preg_match('!^\d{4}-\d{2}-\d{2}$!',$date))
      $date = strftime('%Y-%m-%d',strtotime($date));
      
    $data = array(
              'time-entry'=>array(
                'person-id'=>$person_id,
                'date'=>$date,
                'hours'=>$hours,
                'description'=>$description
                )
            );
    
    $this->setupRequestBody($data);
    
    $response = $this->processRequest("{$this->baseurl}/time_entries/{$entry_id}.xml","PUT");
    
    return $response;
    
  }
  
  /**
   * deletes a time entry
   *
   * @param int $entry_id
   * @return array response content
   */  
  public function deleteTimeEntry($entry_id) {
    if(!preg_match('!^\d+$!',$entry_id))
      throw new InvalidArgumentException("entry id must be a number.");
    return $this->processRequest("{$this->baseurl}time_entries/{$entry_id}.xml","DELETE");
  }
  
  /**
   * get a time entry report
   *
   * @param date $from  format YYYYMMDD or parsible by strtotime()
   * @param date $to  format YYYYMMDD or parsible by strtotime()
   * @param int subject_id person id to restrict time entries to
   * @param int to_item_id related todo item id to restrict to
   * @param int filter_project_id project id to restrict to
   * @param int filter_company_id company id to restrict to
   * @return array response content
   */  
  public function getTimeEntryReport(
    $from=null,
    $to=null,
    $subject_id=null,
    $to_item_id=null,
    $filter_project_id=null,
    $filter_company_id=null) {
    
    // if date not valid format, try to guess it
    if(isset($from)&&!preg_match('!^\d{8}$!',$from))
      $from = strftime('%Y%m%d',strtotime($from));
    if(isset($to)&&!preg_match('!^\d{8}$!',$to))
      $to = strftime('%Y%m%d',strtotime($to));
    
    return $this->processRequest(sprintf("{$this->baseurl}time_entries/report.xml?from=%s&to=%s&subject_id=%s&to_item_id=%s&filter_project_id=%s&filter_company_id=%s",
      $from,
      $to,
      (int)$subject_id,
      (int)$to_item_id,
      (int)$filter_project_id,
      (int)$filter_company_id      
      ),"GET");
  } 

  /**
   * Copy milestones and to-do lists/items from one project to another.
   * Note: both projects must exist in Basecamp. Be sure to create
   * A blank project to copy into. All responsible parties and milestone
   * dates are preserved. After the project is copied, you can adjust the
   * date of the first milestone and tell Basecamp to push the dates
   * of the other milestones forward (from the web interface.)
   *
   * @param int $from_project_id
   * @param int $to_project_id
   * @return array response content
   */  
  public function copyProject($from_project_id,$to_project_id) {
  	
    if(!preg_match('!^\d+$!',$from_project_id))
      throw new InvalidArgumentException("from project id must be a number.");
    if(!preg_match('!^\d+$!',$to_project_id))
      throw new InvalidArgumentException("to project id must be a number.");
   	
    // grab the milestones from the first project
    $response = $this->getMilestonesForProject($from_project_id,'all','simplexml');
    
    if($response['status'] != 200) {
      throw new InvalidArgumentException("unable to retrieve project '{$from_project_id}'.");    	
    }
    $milestone_link = array();
    // copy milestones to new project, keep a list of old_id=>new_id
    foreach($response['body']->milestone as $milestone) {
    	$response_makemile = $this->createMilestoneForProject(
    		$to_project_id,
    		$milestone->title,
    		$milestone->deadline,
    		$milestone->{'responsible-party-type'},
    		$milestone->{'responsible-party-id'},
    		$milestone->{'wants-notification'}
    		);
    	if($response_makemile['status'] != 201) {
    		throw new InvalidArgumentException("unable to create new milestone: '{$response_makemile['body']}'");   	    		
    	}
    	$id = (int) $milestone->id;
    	$milestone_link[$id] = (int)$response_makemile['id'];
    	
    	$this->sleeper();
    	
    }
    
		// copy to-do lists
		$response_lists = $this->getTodoListsForProject($from_project_id,'all','simplexml');

		if($response_lists['status'] != 200) {
			throw new InvalidArgumentException("unable to get to-do lists: '{$response_lists['body']}'");   	    		
		}
		
		foreach($response_lists['body']->{'todo-list'} as $list) {
			$orig_ms_id = (int)$list->{'milestone-id'};
			$new_ms_id = $milestone_link[$orig_ms_id];
			$response_makelist = $this->createTodoListForProject(
				$to_project_id,
				$list->name,
				$list->description,
				$new_ms_id,
				$list->private,
				$list->tracked
				);

			if($response_makelist['status'] != 201) {
				throw new InvalidArgumentException("unable to create to-do list: '{$response_makelist['body']}'");   	    		
			}
			
			$todo_list_id = $response_makelist['id'];

			// copy to-do list items
			
      $response_items = $this->getTodoItemsForList($list->id,'simplexml');
      
			if($response_items['status'] != 200) {
				throw new InvalidArgumentException("unable to get to-do list items: '{$response_items['body']}'");   	    		
			}
			
			foreach($response_items['body']->{'todo-item'} as $item) {
				$response_maketodo = $this->createTodoItemForList(
					$todo_list_id,
					$item->content,
					$item->{'responsible-party-type'},
					$item->{'responsible-party-id'},
					false // do not notify
			  );
			  
			  $this->sleeper();
				
			}
			
			$this->sleeper();
			
		}
    
  } 
  
  /**
   * sleep every so often so basecamp
   * doesn't complain about flooding.
   *
   * @return string $username
   */  
  private function sleeper()
  {
  	static $counter = 0;
  	
  	if($counter > 2) {
  		sleep(1);
  		$counter = 0;
  	} else {
  		$counter++;
  	}
  }  
  
  /* setters and getters */  
  
  /**
   * get username
   *
   * @return string $username
   */  
  public function getUsername()
  {
    return $this->username;
  }

  /**
   * set username
   *
   * @param string $username
   */  
  public function setUsername($username)
  {
    if(empty($username))
      throw new InvalidArgumentException("username cannot be empty.");
    $this->username = $username;
  }  
  
  /**
   * get format
   *
   * @return string $format
   */  
  public function getFormat()
  {
    return $this->format;
  }

  /**
   * set format
   *
   * @param string $format
   */  
  public function setFormat($format)
  {
    if(empty($format))
      throw new InvalidArgumentException("format cannot be empty.");
  	$format = strtolower($format);
    if(!in_array($format,array('xml','simplexml')))
      throw new InvalidArgumentException("'{$format}' is not a valid format.");
    $this->format = $format;
  }  

  /**
   * get password
   *
   * @return string $password
   */  
  public function getPassword()
  {
    return $this->password;
  }

  /**
   * set password
   *
   * @param string $password
   */  
  public function setPassword($password)
  {
    if(empty($password))
      throw new InvalidArgumentException("password cannot be empty.");
    $this->password = $password;
  }  

  
  /**
   * get request body
   *
   * @return string $request_body
   */  
  public function getRequestBody()
  {
    return $this->request_body;
  }

  /**
   * set request body
   *
   * @param string $body
   */  
  public function setRequestBody($body)
  {
    $this->request_body = $body;
  }  

  /**
   * get baseurl
   *
   * @return string $baseurl
   */  
  public function getBaseurl()
  {
    return $this->baseurl;
  }

  /**
   * set baseurl
   *
   * @param string $baseurl
   */  
  public function setBaseurl($url)
  {
    if(empty($url))
      throw new InvalidArgumentException("Base URL cannot be empty.");
    // add default http protocol if absent
    if(!preg_match('!^https?://!i',$url))
      $url = 'http://' . $url;
    // add trailing slash if necessary
    if(substr($url,-1) !== '/')
      $url .= '/';
    $this->baseurl = $url;
  }
  
  /* private methods */

  /**
   * setup the REST request body
   *
   * @param string $body
   */  
  private function setupRequestBody($body) {
    $request_body = array('request'=>$body);
    $this->setRequestBody($this->createXMLFromArray($request_body));
  }  
  
  /**
   * process the current REST request
   *
   * @param string $url url to API request
   * @param string $type type of request (GET/PUT/POST/DELETE)
   * @param string $format format of response (xml/simplexml)
   * @return array $return response array
   */  
  private function processRequest($url,$type,$format=null) {
    
    $this->request = new RestRequest($url,$type);
    $this->request->setUsername($this->username);
    $this->request->setPassword($this->password);

    $this->request->setRequestBody($this->request_body);
    
    $this->request->execute();
    
    $response_info = $this->request->getResponseInfo();
    $response_content = $this->request->getResponseBody();

    $return['headers'] =   substr($response_content,0,$response_info['header_size']);
    $return['body'] = substr($response_content,$response_info['header_size']);
    
    // grab status from headers
    if(preg_match('!^Status: (.*)$!m',$return['headers'],$match))
      $return['status'] = trim($match[1]);
    else
      $return['status'] = null;

    // grab location from headers
    if(preg_match('!^Location: (.*)$!m',$return['headers'],$match))
      $return['location'] = trim($match[1]);
    else
      $return['location'] = null;
      
    // set output format
    if(!isset($format))
      $format = $this->format;
      
    $return['body'] = trim($return['body']);
    if(!empty($return['body']) && $format == 'simplexml') {
      // return simplexml object
      $return['body'] = new SimpleXMLElement($return['body']);
    }
    
    // finished with request, release it
    unset($this->request);
    // clear the request body contents
    $this->request_body = null;
    
    return $return;
  }
  
  /**
   * create XML from PHP array (recursive)
   *
   * @param array $array php arrays (of arrays) of values
   * @return string $xml
   */  
  private function createXMLFromArray($array,$level=0) {
    $xml = '';
    foreach($array as $key=>$val) {
        $attrs = '';
        // separate attributes if any
        if(($spos = strpos($key,' '))!==false) {
          $attrs = substr($key,$spos);
          $key = substr($key,0,$spos);
        }
        // hack to take multiple same-named keys :)
        if(($colpos = strpos($key,':'))!==false)
          $key = substr($key,0,$colpos);
        // add to xml string. if array, recurse.
        $xml .= sprintf("%s<%s>%s</%s>\n",
          str_repeat('  ',$level),
          htmlspecialchars($key).$attrs,
          is_array($val) ? "\n".$this->createXMLFromArray($val,$level+1).str_repeat('  ',$level) : htmlspecialchars($val),
          htmlspecialchars($key)
        );
    }
    return $xml;
  }
  
}
