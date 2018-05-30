<?php

namespace SuperAwesome\Blog\Domain\Model\Post;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use SuperAwesome\Blog\Domain\Model\Post\Event\PostWasCategorized;
use SuperAwesome\Blog\Domain\Model\Post\Event\PostWasCreated;
use SuperAwesome\Blog\Domain\Model\Post\Event\PostWasPublished;
use SuperAwesome\Blog\Domain\Model\Post\Event\PostWasTagged;
use SuperAwesome\Blog\Domain\Model\Post\Event\PostWasUncategorized;
use SuperAwesome\Blog\Domain\Model\Post\Event\PostWasUntagged;
use SuperAwesome\Common\Domain\Model\EventSourcing;

/**
 * TO MAKE TESTS PASS, COMMENT OUT extends, UN-COMMENT EventSourcing,
 * AND REPLACE "apply(" WITH "recordEvent("
 */
class Post
    extends EventSourcedAggregateRoot
{
//    use EventSourcing;

    /** @var string */
    private $id;

    /** @var string */
    private $title;

    /** @var string */
    private $content;

    /** @var string */
    private $category;

    /** @var bool[] */
    private $tags = [];

    /**
     * @param string $id
     */
    private function __construct()
    {

    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return array_keys($this->tags);
    }

    /**
     * Publish a post.
     *
     * @param $title
     * @param $content
     * @param $category
     */
    public function publish($title, $content, $category)
    {
        if ($this->title == $title
            && $this->content == $content
            && $this->category == $category) {
            return;
        }

        $this->uncategorizeIfCategoryChanged($category);
        $this->categorizeIfCategoryChanged($category);

        $this->apply(new PostWasPublished(
           $this->id,
           $title,
           $content,
           $this->category
        ));
    }

    protected function uncategorizeIfCategoryChanged($category)
    {
        if ($category === $this->category || !$this->category) {
            return;
        }

        $this->apply(new PostWasUncategorized($this->id, $this->category));
    }

    protected function categorizeIfCategoryChanged($category)
    {
        if ($category === $this->category) {
            return;
        }

        $this->apply(new PostWasCategorized($this->id, $category));
    }

    /**
     * Tag a post.
     *
     * @param string $tag
     */
    public function addTag($tag)
    {
        if (isset($this->tags[$tag]) && $this->tags[$tag]) {
            return;
        }

        $this->apply(new PostWasTagged($this->id, $tag));
    }

    /**
     * Untag a post.
     *
     * @param string $tag
     */
    public function removeTag($tag)
    {
        if (!isset($this->tags[$tag]) || !$this->tags[$tag]) {
            return;
        }

        $this->apply(new PostWasUntagged($this->id, $tag));
    }

    static public function create($id)
    {
        $instance = new static();
        $instance->apply(new PostWasCreated($id));

        return $instance;
    }

    static public function instantiateForReconstitution()
    {
        return new static();
    }

    public function applyPostWasCreated(PostWasCreated $event)
    {
        $this->id = $event->id;
    }

    public function applyPostWasCategorized(PostWasCategorized $event)
    {
        $this->category = $event->category;
    }

    public function applyPostWasUncategorized(PostWasUncategorized $event)
    {
        $this->category = null;
    }

    public function applyPostWasPublished(PostWasPublished $event)
    {
        $this->title = $event->title;
        $this->content = $event->content;
    }

    public function applyPostWasTagged(PostWasTagged $event)
    {
        $this->tags[$event->tag] = true;
    }

    public function applyPostWasUntagged(PostWasUntagged $event)
    {
        if (isset($this->tags[$event->tag])) {
            unset($this->tags[$event->tag]);
        }
    }

    public function getAggregateRootId(): string
    {
        return $this->id;
    }
}
