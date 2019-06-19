<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Comment\Business\Writer;

use ArrayObject;
use Generated\Shared\Transfer\CommentResponseTransfer;
use Generated\Shared\Transfer\CommentTagRequestTransfer;
use Generated\Shared\Transfer\CommentTagTransfer;
use Generated\Shared\Transfer\CommentTransfer;
use Generated\Shared\Transfer\MessageTransfer;
use Spryker\Zed\Comment\Business\Reader\CommentThreadReaderInterface;
use Spryker\Zed\Comment\CommentConfig;
use Spryker\Zed\Comment\Persistence\CommentEntityManagerInterface;
use Spryker\Zed\Comment\Persistence\CommentRepositoryInterface;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;

class CommentTagWriter implements CommentTagWriterInterface
{
    use TransactionTrait;

    protected const GLOSSARY_KEY_COMMENT_NOT_FOUND = 'comment.validation.error.comment_not_found';
    protected const GLOSSARY_KEY_COMMENT_TAG_NOT_AVAILABLE = 'comment.validation.error.comment_tag_not_available';

    /**
     * @var \Spryker\Zed\Comment\Persistence\CommentEntityManagerInterface
     */
    protected $commentEntityManager;

    /**
     * @var \Spryker\Zed\Comment\Persistence\CommentRepositoryInterface
     */
    protected $commentRepository;

    /**
     * @var \Spryker\Zed\Comment\Business\Reader\CommentThreadReaderInterface
     */
    protected $commentThreadReader;

    /**
     * @var \Spryker\Zed\Comment\CommentConfig
     */
    protected $commentConfig;

    /**
     * @param \Spryker\Zed\Comment\Persistence\CommentEntityManagerInterface $commentEntityManager
     * @param \Spryker\Zed\Comment\Persistence\CommentRepositoryInterface $commentRepository
     * @param \Spryker\Zed\Comment\Business\Reader\CommentThreadReaderInterface $commentThreadReader
     * @param \Spryker\Zed\Comment\CommentConfig $commentConfig
     */
    public function __construct(
        CommentEntityManagerInterface $commentEntityManager,
        CommentRepositoryInterface $commentRepository,
        CommentThreadReaderInterface $commentThreadReader,
        CommentConfig $commentConfig
    ) {
        $this->commentEntityManager = $commentEntityManager;
        $this->commentRepository = $commentRepository;
        $this->commentThreadReader = $commentThreadReader;
        $this->commentConfig = $commentConfig;
    }

    /**
     * @param \Generated\Shared\Transfer\CommentTagRequestTransfer $commentTagRequestTransfer
     *
     * @return \Generated\Shared\Transfer\CommentResponseTransfer
     */
    public function addCommentTag(CommentTagRequestTransfer $commentTagRequestTransfer): CommentResponseTransfer
    {
        $commentTagRequestTransfer
            ->requireName()
            ->requireComment()
            ->getComment()
                ->requireUuid();

        if (!$this->isCommentTagAvailable($commentTagRequestTransfer)) {
            return $this->createErrorResponse(static::GLOSSARY_KEY_COMMENT_TAG_NOT_AVAILABLE);
        }

        $commentTransfer = $this->commentRepository->findCommentByUuid($commentTagRequestTransfer->getComment());

        if (!$commentTransfer) {
            return $this->createErrorResponse(static::GLOSSARY_KEY_COMMENT_NOT_FOUND);
        }

        $commentTagTransfer = (new CommentTagTransfer())
            ->setName($commentTagRequestTransfer->getName());

        $commentTransfer->addCommentTag($commentTagTransfer);
        $commentTransfer = $this->saveCommentTags($commentTransfer);

        return (new CommentResponseTransfer())
            ->setIsSuccessful(true)
            ->setComment($commentTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\CommentTagRequestTransfer $commentTagRequestTransfer
     *
     * @return \Generated\Shared\Transfer\CommentResponseTransfer
     */
    public function removeCommentTag(CommentTagRequestTransfer $commentTagRequestTransfer): CommentResponseTransfer
    {
        $commentTagRequestTransfer
            ->requireName()
            ->requireComment()
            ->getComment()
                ->requireUuid();

        if (!$this->isCommentTagAvailable($commentTagRequestTransfer)) {
            return $this->createErrorResponse(static::GLOSSARY_KEY_COMMENT_TAG_NOT_AVAILABLE);
        }

        $commentTransfer = $this->commentRepository->findCommentByUuid($commentTagRequestTransfer->getComment());

        if (!$commentTransfer) {
            return $this->createErrorResponse(static::GLOSSARY_KEY_COMMENT_NOT_FOUND);
        }

        $commentTagTransfers = [];

        foreach ($commentTransfer->getTags() as $commentTagTransfer) {
            if ($commentTagTransfer->getName() !== $commentTagRequestTransfer->getName()) {
                $commentTagTransfers[] = $commentTagTransfer;
            }
        }

        $commentTransfer->setTags(new ArrayObject($commentTagTransfers));
        $commentTransfer = $this->saveCommentTags($commentTransfer);

        return (new CommentResponseTransfer())
            ->setIsSuccessful(true)
            ->setComment($commentTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\CommentTransfer $commentTransfer
     *
     * @return \Generated\Shared\Transfer\CommentTransfer
     */
    public function saveCommentTags(CommentTransfer $commentTransfer): CommentTransfer
    {
        $expandedCommentTagTransfers = [];
        $commentTagMap = $this->mapCommentTagsByName($this->commentRepository->getAllCommentTags());

        foreach ($commentTransfer->getTags() as $commentTagTransfer) {
            if (!isset($commentTagMap[$commentTagTransfer->getName()])) {
                $commentTagMap[$commentTagTransfer->getName()] = $this->commentEntityManager->createCommentTag($commentTagTransfer);
            }

            $expandedCommentTagTransfers[] = $commentTagMap[$commentTagTransfer->getName()];
        }

        $commentTransfer->setTags(new ArrayObject($expandedCommentTagTransfers));

        $this->commentEntityManager->addCommentTagsToComment($commentTransfer);
        $this->commentEntityManager->removeCommentTagsFromComment($commentTransfer);

        return $commentTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\CommentTagTransfer[] $commentTagTransfers
     *
     * @return \Generated\Shared\Transfer\CommentTagTransfer[]
     */
    protected function mapCommentTagsByName(array $commentTagTransfers): array
    {
        $commentTagMap = [];

        foreach ($commentTagTransfers as $commentTagTransfer) {
            $commentTagMap[$commentTagTransfer->getName()] = $commentTagTransfer;
        }

        return $commentTagMap;
    }

    /**
     * @param \Generated\Shared\Transfer\CommentTagRequestTransfer $commentTagRequestTransfer
     *
     * @return bool
     */
    protected function isCommentTagAvailable(CommentTagRequestTransfer $commentTagRequestTransfer): bool
    {
        return in_array($commentTagRequestTransfer->getName(), $this->commentConfig->getCommentAvailableTags(), true);
    }

    /**
     * @param string $message
     *
     * @return \Generated\Shared\Transfer\CommentResponseTransfer
     */
    protected function createErrorResponse(string $message): CommentResponseTransfer
    {
        $messageTransfer = (new MessageTransfer())
            ->setValue($message);

        return (new CommentResponseTransfer())
            ->setIsSuccessful(false)
            ->addMessage($messageTransfer);
    }
}
