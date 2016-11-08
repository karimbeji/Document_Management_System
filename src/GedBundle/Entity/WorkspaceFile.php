<?php

namespace GedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WorkspaceFile
 *
 * @ORM\Table(name="workspace_file")
 * @ORM\Entity(repositoryClass="GedBundle\Repository\WorkspaceFileRepository")
 */
class WorkspaceFile
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     *
     * @ORM\ManyToOne(targetEntity="Workspace", inversedBy="files")
     * @ORM\JoinColumn(nullable=false)
     *
     */
    private $workspace;


    /**
     *
     * @ORM\ManyToOne(targetEntity="File", inversedBy="workspaces")
     * @ORM\JoinColumn(nullable=false)
     *
     */
    private $file;


    /**
     * @var int
     *
     * @ORM\Column(name="rating", type="smallint", nullable=true)
     */
    private $rating;

    /**
     * @var int
     *
     * @ORM\Column(name="notification", type="smallint", nullable=true)
     */
    private $notification;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param int $rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    /**
     * @return int
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param int $notification
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;
    }


    /**
     * @return mixed
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * @param Workspace $workspace
     */
    public function setWorkspace($workspace)
    {
        $this->workspace = $workspace;
        $workspace->addFile($this);
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param File $file
     */
    public function setFile($file)
    {
        $this->file = $file;
        $file->addWorkspace($this);
    }

}
