<?php
/**
 * Copyright (c) 2010, Christoph Gockel.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * * Neither the name of PiBX nor the names of its contributors may be used
 *   to endorse or promote products derived from this software without specific
 *   prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
require_once 'PiBX/AST/Tree.php';
/**
 * A concrete or abstract type.
 *
 * Concrete type means, it can occur as a root element in the corresponding
 * XML file. Abstract is used for complexTypes that are used for structure
 * references.
 *
 * @author Christoph Gockel
 */
class PiBX_AST_Type extends PiBX_AST_Tree {
    /**
     * @var int How many attributes/members the current Type has
     */
    private $attributeCount;
    /**
     * @var boolean Wether the current Type is the root type of the XSD or not.
     */
    private $isRootType;

    /**
     * @var string 
     */
    private $targetNamespace;
    /**
     * @var array Associative array of used namespaces: prefix => URI
     */
    private $namespaces;

    private $valueStyle;

    public function  __construct($name = '', $type = '') {
        parent::__construct($name, $type);

        $this->isRootType = false;
        $this->valueStyle = 'element';
    }

    public function setAsRoot() {
        $this->isRootType = true;
    }
    public function isRoot() {
        return $this->isRootType;
    }

    public function setAttributeCount($count) {
        //$this->attributeCount = $count;
    }
    public function getAttributeCount() {

    }
    
    public function setTargetNamespace($targetNamespace) {
        $this->targetNamespace = $targetNamespace;
    }
    public function getTargetNamespace() {
        return $this->targetNamespace;
    }

    public function setNamespaces(array $namespaces = null) {
        $this->namespaces = $namespaces;
    }
    public function getNamespaces() {
        return $this->namespaces;
    }

    public function setValueStyle($valueStyle) {
        $this->valueStyle = $valueStyle;
    }
    public function getValueStyle() {
        return $this->valueStyle;
    }

    public function isEnumerationType() {
        if (!$this->hasChildren()) {
            return false;
        }

        $firstEnumeration = $this->get(0);
        if ($firstEnumeration instanceof PiBX_AST_Enumeration) {
            return true;
        }

        return false;
    }

    public function isStandardType() {
        if (!$this->hasChildren()) {
            return true;
        }

        $firstAttribute = $this->get(0);
        if ($firstAttribute instanceof PiBX_AST_TypeAttribute) {
            return true;
        }

        return false;
    }

    public function accept(PiBX_AST_Visitor_VisitorAbstract $v) {
        if ($v->visitTypeEnter($this)) {
            foreach ($this->children as $child) {
                if ($child->accept($v) === false) {
                    break;
                }
            }
        }

        return $v->visitTypeLeave($this);
    }
}
