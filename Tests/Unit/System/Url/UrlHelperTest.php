<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace ApacheSolrForTypo3\Solr\Tests\Unit\System\Url;

use ApacheSolrForTypo3\Solr\System\Url\UrlHelper;
use ApacheSolrForTypo3\Solr\Tests\Unit\SetUpUnitTestCase;
use Traversable;

/**
 * Testcase to check the functionallity of the UrlHelper
 *
 * @author Timo Hund <timo.hund@dkd.de>
 */
class UrlHelperTest extends SetUpUnitTestCase
{
    public static function withoutQueryParameter(): Traversable
    {
        yield 'cHash at the end' => [
            'input' => 'index.php?id=1&cHash=ddd',
            'queryParameterToRemove' => 'cHash',
            'expectedUrl' => '/index.php?id=1',
        ];
        yield 'cHash at the beginning' => [
            'input' => 'index.php?cHash=ddd&id=1',
            'queryParameterToRemove' => 'cHash',
            'expectedUrl' => '/index.php?id=1',
        ];
        yield 'cHash in the middle' => [
            'input' => 'index.php?foo=bar&cHash=ddd&id=1',
            'queryParameterToRemove' => 'cHash',
            'expectedUrl' => '/index.php?foo=bar&id=1',
        ];
        yield 'result is urlencoded' => [
            'input' => 'index.php?foo[1]=bar&cHash=ddd&id=1',
            'queryParameterToRemove' => 'cHash',
            'expectedUrl' => '/index.php?foo%5B1%5D=bar&id=1',
        ];
        yield 'result is urlencoded with unexisting remove param' => [
            'input' => 'index.php?foo[1]=bar&cHash=ddd&id=1',
            'queryParameterToRemove' => 'notExisting',
            'expectedUrl' => '/index.php?foo%5B1%5D=bar&cHash=ddd&id=1',
        ];
    }
    /**
     * @dataProvider withoutQueryParameter
     * @test
     */
    public function testCanRemoveQueryParameter($input, $queryParameterToRemove, $expectedUrl)
    {
        $urlHelper = new UrlHelper($input);
        $urlHelper = $urlHelper->withoutQueryParameter($queryParameterToRemove);
        self::assertSame($expectedUrl, (string)$urlHelper, 'Can not remove query parameter as expected');
    }

    public static function getUrl(): Traversable
    {
        yield 'nothing should be changed' => [
            'inputUrl' => 'index.php?foo%5B1%5D=bar&cHash=ddd&id=1',
            'expectedOutputUrl' => '/index.php?foo%5B1%5D=bar&cHash=ddd&id=1',
        ];
        yield 'url should be encoded' => [
            'inputUrl' => 'index.php?foo[1]=bar&cHash=ddd&id=1',
            'expectedOutputUrl' => '/index.php?foo%5B1%5D=bar&cHash=ddd&id=1',
        ];
        yield 'url with https protocol' => [
            'inputUrl' => 'https://www.google.de/index.php',
            'expectedOutputUrl' => 'https://www.google.de/index.php',
        ];
        yield 'url with port' => [
            'inputUrl' => 'http://www.google.de:8080/index.php',
            'expectedOutputUrl' => 'http://www.google.de:8080/index.php',
        ];
    }

    /**
     * @dataProvider getUrl
     * @test
     * @param string $inputUrl
     * @param string $expectedOutputUrl
     */
    public function testGetUrl($inputUrl, $expectedOutputUrl)
    {
        $urlHelper = new UrlHelper($inputUrl);
        self::assertSame($expectedOutputUrl, (string)$urlHelper, 'Can not get expected output url');
    }

    public static function unmodifiedUrl(): Traversable
    {
        yield 'noQuery' => ['http://www.site.de/en/test'];
        yield 'withQuery' => ['http://www.site.de/en/test?id=1'];
        yield 'withQueries' => ['http://www.site.de/en/test?id=1&L=2'];
    }
    /**
     * @dataProvider unmodifiedUrl
     */
    public function testGetUnmodifiedUrl($uri)
    {
        $urlHelper = new UrlHelper($uri);
        self::assertSame($uri, (string)$urlHelper, 'Could not get unmodified url');
    }

    /**
     * @test
     */
    public function ifNoSchemeIsGivenGetSchemeReturnsAnEmptyString(): void
    {
        $urlHelper = new UrlHelper('www.google.de');
        self::assertSame('', $urlHelper->getScheme());
    }

    /**
     * @test
     */
    public function ifNoPathIsGivenGetPathReturnsAnEmptyString(): void
    {
        $urlHelper = new UrlHelper('https://www.google.de');
        self::assertSame('', $urlHelper->getPath());
    }

    /**
     * @test
     */
    public function ifNoPortIsGivenGetPortReturnsAnEmptyString(): void
    {
        $urlHelper = new UrlHelper('https://www.google.de');
        self::assertNull($urlHelper->getPort());
    }

    /**
     * @test
     */
    public function ifNoHostIsGivenGetHostReturnsAnEmptyString(): void
    {
        $urlHelper = new UrlHelper('/my/path/to/a/site');
        self::assertSame('', $urlHelper->getHost());
    }
}
