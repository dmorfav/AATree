<?php

/**
 * Created by PhpStorm.
 * User: dmorfav
 * Date: 22/03/17
 * Time: 8:37
 */
class AA
{
    const LEVELEAFT = 1;

    private $left;

    private $right;

    private $key;

    private $item;

    public function __construct($key,$item)
    {
        $this->left = null;
        $this->right = null;
        $this->item = $item;
        ($key == null)?$this->key = AA::LEVELEAFT:$this->key = $key;
    }


    //walk() recursively traverses a tree made of these nodes
    //Then it renders it out visually to the console
    public function walk($tree, $depth, $side) { //depth and side are used for internal record-keeping
        if (!$depth) $depth = 0;
        if (!$side) $side = "";
        $padding = "";
        for ($i = 0; $i < $depth; $i++) $padding += "| "; //add spacing to the output
          var_dump($padding, $side, $tree->key, $tree->data); //display the tree
          //now we walk down the branches, if they exist
          if ($tree->left) $this->walk($tree->left, $depth + 1, 'left');
          if ($tree->right) $this->walk($tree->right, $depth + 1, 'right');
    }

    public function skew ($branch) {
        if (!$branch || !$branch->left) return $branch; //basic input validation
        if ($branch->left->level >= $branch->level) {
            $swap = $branch->left;
            $branch->left = $swap->right;
            $swap->right = $branch;
            return $swap;
        }
        return $branch;
    }

    public function split($branch) {
        //basic input validation
        if (!$branch || !$branch->right || !$branch->right->right) return $branch;
        if ($branch->right->right->level >= $branch->level) {
            $swap = $branch->right;
            $branch->right = $swap->left;
            $swap->left = $branch;
            $swap->level++;
            return $swap;
        }
        return $branch;
    }

    public function Insert ($key, $data, $branch)  {
        if ($branch->key == $key) {
            $branch->data = $data;
            return $branch; //If we find a match for the key, just update in place
        }
        if ($key < $branch->key) {
            if ($branch->left) {
                $branch->left = $this->Insert($key, $data, $branch->left); //recurse to the left
            } else {
                $branch->left = new AA($key, $data); //at the leaf, add the new node.
            }
        } else {
            if ($branch->right) {
                $branch->right = $this->Insert($key, $data, $branch->right); //recurse to the right
            } else {
                $branch->right = new AA($key, $data); //at the leaf, add the new node.
            }
        }
        $branch = $this->skew($branch);
        $branch = $this->split($branch);
        return $branch;
    }

    public function Remove($key, $branch) {
        if (!$branch) return $branch; //if we recurse past the end of the tree, return the null value.
        if ($key < $branch->key) {
            $branch->left = $this->Remove($key, $branch->left);
        } else if ($key > $branch->key) {
            $branch->right = $this->Remove($key, $branch->right);
        } else {
            //if this is a leaf node, we can just return "null" to the recursive assignment to remove it.
            if (!$branch->left && !$branch->right) return null;
            //we look for the "closest" key in value, located at the end of one of the child branches
            $parent = $branch;
            if ($branch->left) {
                $replacement = $branch->left;
                while ($replacement->right) {
                    $parent = $replacement;
                    $replacement = $replacement->right;
                }
            } else if ($branch->right) {
                $replacement = $branch->right;
                while ($replacement->left) {
                    $parent = $replacement;
                    $replacement = $replacement->left;
                }
            }
            //we swap the replacement key and data into the to-be-removed node
            $branch->key = $replacement->key;
            $branch->item = $replacement->item;
            //then remove the replacement node from the tree, thus completing the "swap"
            //we can't do this in the while loop above, because technically it could
            //be either child, regardless of initial search direction.
            if ($parent->left == $replacement) {
                $parent->left = null;
            } else {
                $parent->right = null;
            }
        }

        //decrease levels, in case they've gotten out of control
        $minLevel = $branch->left ? $branch->left->level : $branch->level;
        $minLevel = $branch->right && $branch->right->level + 1 < $minLevel ?
            $branch->right->level + 1 :
            $minLevel;
        if ($minLevel < $branch->level) {
            $branch->level = $minLevel;
            if ($branch->right && $minLevel < $branch->right->level)
                $branch->right->level = $minLevel;
        }

        //rebalance, using the sequence given in Andersson's paper.
        $branch = $this->skew($branch);
        $branch->right = $this->skew($branch->right);
        if ($branch->right) $branch->right->right = $this->skew($branch->right->right);
        $branch = $this->split($branch);
        $branch->right = $this->split($branch->right);
        return $branch;
    }
}