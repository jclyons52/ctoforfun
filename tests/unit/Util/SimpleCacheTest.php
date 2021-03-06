<?php

namespace Gilbitron\Util;

use Mockery;
use PHPieces\Framework\Util\File;
use PHPieces\Framework\Util\Http;

class SimpleCacheTest extends \PHPUnit_Framework_TestCase
{
    private $cache_path = '/path/to/cache/';

    private $label = 'http://www.meetup.com/Melbourne-PHP-Users-Group/events/224111131/';

    private $safe_label = 'httpwww.meetup.commelbourne-php-users-groupevents224111131';

    private $data = 'foo bar';

    private $cache_file;

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->cache_file = "{$this->cache_path}{$this->safe_label}.cache";
    }

    /**
     * @test
     */
    public function it_sets_cache_content()
    {
        $file = Mockery::mock(File::class);

        $file->shouldReceive('put')->once()->with($this->cache_file, $this->data);

        $http = Mockery::mock(Http::class);

        $cache = new SimpleCache($this->cache_path, $file, $http);

        $cache->set($this->label, $this->data);
    }

    /**
     * @test
     */
    public function it_returns_false_if_file_is_not_cached()
    {
        $file = Mockery::mock(File::class);

        $file->shouldReceive('exists')->andReturn(false);

        $http = Mockery::mock(Http::class);

        $cache = new SimpleCache($this->cache_path, $file, $http);

        $cache->get($this->label);
    }

    /**
     * @test
     */
    public function it_returns_item_from_the_cache_if_it_exists()
    {
        $file = Mockery::mock(File::class);

        $file->shouldReceive('exists')->andReturn(true);

        $file->shouldReceive('time')->andReturn(time());

        $file->shouldReceive('get')->with($this->cache_file)->andReturn($this->data);

        $http = Mockery::mock(Http::class);

        $cache = new SimpleCache($this->cache_path, $file, $http);

        $data = $cache->get($this->label);

        $this->assertEquals($this->data, $data);
    }

    /**
     * @test
     */
    public function it_makes_http_call_if_file_is_not_cached()
    {
        $file = Mockery::mock(File::class);

        $file->shouldReceive('exists')->andReturn(false);

        $file->shouldReceive('put')->withArgs([$this->cache_file, $this->data]);

        $http = Mockery::mock(Http::class);

        $http->shouldReceive('get')->with($this->label)->andReturn($this->data);

        $cache = new SimpleCache($this->cache_path, $file, $http);

        $data = $cache->findOrFetch($this->label);

        $this->assertEquals($this->data, $data);
    }
}