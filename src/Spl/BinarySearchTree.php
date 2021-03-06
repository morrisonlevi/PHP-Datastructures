<?php

namespace Spl;

class BinarySearchTree implements Collection {

    const TRAVERSE_IN_ORDER = 1;
    const TRAVERSE_LEVEL_ORDER = 2;
    const TRAVERSE_PRE_ORDER = 3;
    const TRAVERSE_POST_ORDER = 4;

    /**
     * @var BinaryTree
     */
    private $root = NULL;

    /**
     * @var int
     */
    private $size = 0;

    /**
     * @var callable
     */
    protected $comparator;

    /**
     * @param callable $comparator
     */
    function __construct($comparator = NULL) {
        $this->comparator = $comparator !== NULL
            ? $comparator
            : function($a, $b) {
                if ($a < $b) {
                    return -1;
                } elseif ($b < $a) {
                    return 1;
                } else {
                    return 0;
                }
            };
    }

    /**
     * @param mixed $element
     */
    function add($element) {
        $this->root = $this->addNode($element, $this->root);
        $this->cache = NULL;
    }

    /**
     * @param $element
     * @param BinaryTree $node
     *
     * @return null|BinaryTree
     */
    protected function addNode($element, BinaryTree $node = NULL) {
        if ($node === NULL) {
            $this->size++;
            return new BinaryTree($element);
        }

        $comparisonResult = call_user_func($this->comparator, $element, $node->getValue());

        if ($comparisonResult < 0) {
            $node->setLeft($this->addNode($element, $node->getLeft()));
        } else {
            if ($comparisonResult > 0) {
                $node->setRight($this->addNode($element, $node->getRight()));
            }
        }

        return $node;
    }

    /**
     * @param mixed $element
     */
    function remove($element) {
        $this->root = $this->removeNode($element, $this->root);
        $this->cache = NULL;
    }

    /**
     * @param $element
     * @param BinaryTree $node
     *
     * @return BinaryTree
     */
    protected function removeNode($element, BinaryTree $node = NULL) {
        if ($node === NULL) {
            return NULL;
        }

        $comparisonResult = call_user_func($this->comparator, $element, $node->getValue());

        if ($comparisonResult < 0) {
            $node->setLeft($this->removeNode($element, $node->getLeft()));
        } else {
            if ($comparisonResult > 0) {
                $node->setRight($this->removeNode($element, $node->getRight()));
            } else {
                //remove the element
                $node = $this->deleteNode($node);
            }
        }

        return $node;
    }

    /**
     * @param BinaryTree $node
     *
     * @return null|BinaryTree
     */
    protected function deleteNode(BinaryTree $node) {

        if ($node->isLeaf()) {
            $this->size--;
            return NULL;
        }

        if ($node->hasOnlyOneChild()) {
            $this->size--;
            $right = $node->getRight();

            $newNode = $right !== NULL
                ? $right
                : $node->getLeft();

            unset($node);
            return $newNode;
        }

        $value = $node->getInOrderPredecessor()->getValue();

        $node->setLeft($this->removeNode($value, $node->getLeft()));

        $node->setValue($value);

        return $node;
    }

    /**
     * @param $element
     *
     * @return mixed|null the element or NULL if it wasn't found.
     */
    function get($element) {
        return $this->getNode($element, $this->root);
    }

    private function getNode($element, BinaryTree $node = NULL) {
        if ($node === NULL) {
            return NULL;
        }

        $comparisonResult = call_user_func($this->comparator, $element, $node->getValue());

        if ($comparisonResult < 0) {
            return $this->getNode($element, $node->getLeft());
        } elseif ($comparisonResult > 0) {
            return $this->getNode($element, $node->getRight());
        } else {
            return $node->getValue();
        }

    }

    /**
     * @return BinaryTree A copy of the current BinaryTree
     */
    function getBinaryTree() {
        return $this->root !== NULL
            ? clone $this->root
            : NULL;
    }

    /**
     * @return void
     */
    function clear() {
        $this->root = NULL;
        $this->size = 0;
    }

    /**
     * @param $item
     *
     * @return bool
     * @throws TypeException when $item is not the correct type.
     */
    function contains($item) {
        return $this->containsNode($item, $this->root);
    }

    protected function containsNode($element, BinaryTree $node = NULL) {
        if ($node === NULL) {
            return FALSE;
        }

        $comparisonResult = call_user_func($this->comparator, $element, $node->getValue());

        if ($comparisonResult < 0) {
            return $this->containsNode($element, $node->getLeft());
        } elseif ($comparisonResult > 0) {
            return $this->containsNode($element, $node->getRight());
        } else {
            return TRUE;
        }

    }

    /**
     * @return bool
     */
    function isEmpty() {
        return $this->root === NULL;
    }

    /**
     * @var BinaryTree
     */
    private $cache = NULL;

    /**
     * @param int $order [optional]
     *
     * @return BinaryTreeIterator
     */
    function getIterator($order = self::TRAVERSE_IN_ORDER) {
        $iterator = NULL;

        $root = $this->cache ?: (
            $this->root !== NULL
                ? clone $this->root
                : NULL
        );

        switch ($order) {
            case self::TRAVERSE_LEVEL_ORDER:
                $iterator = new LevelOrderIterator($root);
                break;

            case self::TRAVERSE_PRE_ORDER:
                $iterator = new PreOrderIterator($root);
                break;

            case self::TRAVERSE_POST_ORDER:
                $iterator = new PostOrderIterator($root);
                break;

            case self::TRAVERSE_IN_ORDER:
            default:
                $iterator = new InOrderIterator($root);
        }

        return $iterator;

    }

    /**
     * @link http://php.net/manual/en/countable.count.php
     * @return int
     */
    function count() {
        return $this->size;
    }

    function __clone() {
        $this->root = $this->root === NULL
            ? NULL
            : clone $this->root;
    }
}
