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
require_once dirname(__FILE__) . '/../bootstrap.php';
require_once 'PHPUnit/Framework.php';
require_once 'PiBX/CodeGen/ASTCreator.php';
require_once 'PiBX/CodeGen/ASTOptimizer.php';
require_once 'PiBX/CodeGen/ClassGenerator.php';
require_once 'PiBX/CodeGen/SchemaParser.php';
require_once 'PiBX/CodeGen/TypeUsage.php';
require_once 'PiBX/Binding/Creator.php';
/**
 * Description of ClassGeneratorTest
 *
 * @author Christoph Gockel
 */
class PiBX_CodeGen_ClassGeneratorTest extends PHPUnit_Framework_TestCase {
    public function testScenarioBooksClasses() {
        $filepath = dirname(__FILE__) . '/../_files/Books';
        $schemaFile = $filepath . '/books.xsd';
        $bindingFile = $filepath . '/binding.xml';
        $collectionFile = $filepath . '/Collection.php';
        $bookTypeFile = $filepath . '/BookType.php';
        
        $typeUsage = new PiBX_CodeGen_TypeUsage();

        // most of this test-case follows the flow of PiBX_CodeGen
        // phase 1
        $parser = new PiBX_CodeGen_SchemaParser($schemaFile, $typeUsage);
        $parsedTree = $parser->parse();

        // phase 2
        $creator = new PiBX_CodeGen_ASTCreator($typeUsage);
        $parsedTree->accept($creator);

        $typeList = $creator->getTypeList();

        // phase 3
        $usages = $typeUsage->getTypeUsages();

        $optimizer = new PiBX_CodeGen_ASTOptimizer($typeList, $typeUsage);
        $typeList = $optimizer->optimize();

        // phase 4
        $b = new PiBX_Binding_Creator();

        foreach ($typeList as &$type) {
            $type->accept($b);
        }

        $this->assertEquals(file_get_contents($bindingFile), $b->getXml());

        // phase 5
        $generator = new PiBX_CodeGen_ClassGenerator();
        foreach ($typeList as &$type) {
            $type->accept($generator);
        }

        $classes = $generator->getClasses();

        $this->assertEquals(2, count($classes));
        $this->assertEquals(file_get_contents($collectionFile), "<?php\n" . $classes['Collection']);
        $this->assertEquals(file_get_contents($bookTypeFile), "<?php\n" . $classes['BookType']);
    }
    
    public function testScenarioBooksClassesWithTypeChecks() {
        $filepath = dirname(__FILE__) . '/../_files/Books';
        $schemaFile = $filepath . '/books.xsd';
        $bindingFile = $filepath . '/binding.xml';
        $collectionFile = $filepath . '/Collection_TypeChecked.php';
        $bookTypeFile = $filepath . '/BookType_TypeChecked.php';

        $typeUsage = new PiBX_CodeGen_TypeUsage();

        // most of this test-case follows the flow of PiBX_CodeGen
        // phase 1
        $parser = new PiBX_CodeGen_SchemaParser($schemaFile, $typeUsage);
        $parsedTree = $parser->parse();

        // phase 2
        $creator = new PiBX_CodeGen_ASTCreator($typeUsage);
        $parsedTree->accept($creator);

        $typeList = $creator->getTypeList();

        // phase 3
        $usages = $typeUsage->getTypeUsages();

        $optimizer = new PiBX_CodeGen_ASTOptimizer($typeList, $typeUsage);
        $typeList = $optimizer->optimize();

        // phase 4
        $b = new PiBX_Binding_Creator();

        foreach ($typeList as &$type) {
            $type->accept($b);
        }

        $this->assertEquals(file_get_contents($bindingFile), $b->getXml());

        // phase 5
        $generator = new PiBX_CodeGen_ClassGenerator();
        $generator->enableTypeChecks();
        
        foreach ($typeList as &$type) {
            $type->accept($generator);
        }

        $classes = $generator->getClasses();

        $this->assertEquals(2, count($classes));
        $this->assertEquals(file_get_contents($collectionFile), "<?php\n" . $classes['Collection']);
        $this->assertEquals(file_get_contents($bookTypeFile), "<?php\n" . $classes['BookType']);
    }
}
