<?php

namespace tests;

use zhuravljov\yii\rest\models\RequestForm;

/**
 * Class RequestFormTest
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class RequestFormTest extends TestCase
{
    /**
     * Mock application prior running tests.
     */
    protected function setUp()
    {
        $this->mockWebApplication();
    }

    public function testParseQueryFromEndpoint()
    {
        $model = new RequestForm([
            'method' => 'get',
            'endpoint' => 'path?a=1&b=2&c=3+4',
            'queryKeys' => [],
            'queryValues' => [],
            'queryActives' => [],
        ]);
        $model->validate();

        $this->assertEquals('path', $model->endpoint);
        $this->assertEquals(['a', 'b', 'c'], $model->queryKeys);
        $this->assertEquals(['1', '2', '3 4'], $model->queryValues);
        $this->assertEquals(['1', '1', '1'], $model->queryActives);
    }

    public function testUri()
    {
        $model = new RequestForm([
            'method' => 'get',
            'endpoint' => 'path',
            'queryKeys' => ['a', 'a', 'b[]', 'b[]'],
            'queryValues' => ['1', '2', '3', 'c d'],
            'queryActives' => ['1', '1', '1', '1'],
        ]);
        $model->validate();

        $this->assertEquals('path?a=1&a=2&b[]=3&b[]=c+d', $model->getUri());
    }

    public function testBodyParams()
    {
        $model = new RequestForm([
            'method' => 'get',
            'endpoint' => 'path',
            'bodyKeys' => ['a', 'a', 'b[]', 'b[]'],
            'bodyValues' => ['1', '2', '3', 'c d'],
            'bodyActives' => ['1', '1', '0', '1'],
        ]);
        $model->validate();

        $this->assertEquals(['a' => 2, 'b' => ['c d']], $model->getBodyParams());
    }

    public function testHeaders()
    {
        $model = new RequestForm([
            'method' => 'get',
            'endpoint' => 'path',
            'headerKeys' => ['a', 'b', 'b'],
            'headerValues' => ['1', '2', '3'],
            'headerActives' => ['1', '1', '1'],
        ]);
        $model->validate();

        $this->assertEquals(['a' => ['1'], 'b' => ['2', '3']], $model->getHeaders());
    }

    public function testAddEmptyRows()
    {
        $model = new RequestForm([
            'method' => 'get',
            'endpoint' => 'path',
        ]);
        $model->addEmptyRows();
        $model->addEmptyRows();

        $this->assertCount(2, $model->queryKeys);
        $this->assertCount(2, $model->bodyKeys);
        $this->assertCount(2, $model->headerKeys);

        $model->validate();

        $this->assertCount(0, $model->queryKeys);
        $this->assertCount(0, $model->bodyKeys);
        $this->assertCount(0, $model->headerKeys);
    }
}