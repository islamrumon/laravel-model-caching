<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Store;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedBook;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;

class BelongsToManyTest extends IntegrationTestCase
{
    public function testLazyLoadingRelationship()
    {
        $bookId = (new Store)
            ->disableModelCaching()
            ->with("books")
            ->first()
            ->books
            ->first()
            ->id;
        $key = sha1("genealabs:laravel-model-caching:testing::memory::book-store:genealabslaravelmodelcachingcachedbelongstomany-book_store.book_id_=_{$bookId}");
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesstore',
        ];

        $stores = (new Book)
            ->find($bookId)
            ->stores;
        $cachedStores = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $uncachedBook = (new UncachedBook)
            ->find($bookId);
        $uncachedStores = $uncachedBook->stores;

        $this->assertEquals($uncachedStores->pluck("id"), $stores->pluck("id"));
        $this->assertEquals($uncachedStores->pluck("id"), $cachedStores->pluck("id"));
        $this->assertNotNull($cachedStores);
        $this->assertNotNull($uncachedStores);
    }

    public function testInvalidatingCacheWhenAttaching()
    {
        $bookId = (new Store)
            ->disableModelCaching()
            ->with("books")
            ->first()
            ->books
            ->first()
            ->id;
        $key = sha1("genealabs:laravel-model-caching:testing::memory::book-store:genealabslaravelmodelcachingcachedbelongstomany-book_store.book_id_=_{$bookId}");
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesstore',
        ];
        $newStore = factory(Store::class)
            ->create();
        $result = (new Book)
            ->find($bookId)
            ->stores;

        (new Book)
            ->find($bookId)
            ->stores()
            ->attach($newStore->id);
        $cachedResult = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];

        $this->assertNotEmpty($result);
        $this->assertNull($cachedResult);
    }

    public function testInvalidatingCacheWhenDetaching()
    {
        $bookId = (new Store)
            ->disableModelCaching()
            ->with("books")
            ->first()
            ->books
            ->first()
            ->id;
        $key = sha1("genealabs:laravel-model-caching:testing::memory::book-store:genealabslaravelmodelcachingcachedbelongstomany-book_store.book_id_=_{$bookId}");
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesstore',
        ];
        $result = (new Book)
            ->find($bookId)
            ->stores;

        (new Book)
            ->find($bookId)
            ->stores()
            ->detach($result->first()->id);
        $cachedResult = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];

        $this->assertNotEmpty($result);
        $this->assertNull($cachedResult);
    }

    public function testInvalidatingCacheWhenUpdating()
    {
        $bookId = (new Store)
            ->disableModelCaching()
            ->with("books")
            ->first()
            ->books
            ->first()
            ->id;
        $key = sha1("genealabs:laravel-model-caching:testing::memory::book-store:genealabslaravelmodelcachingcachedbelongstomany-book_store.book_id_=_{$bookId}");
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesstore',
        ];
        $result = (new Book)
            ->find($bookId)
            ->stores;

        $store = $result->first();
        $store->address = "test address";
        $store->save();
        $cachedResult = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];

        $this->assertNotEmpty($result);
        $this->assertNull($cachedResult);
    }

    public function testUncachedRelatedModelDoesntCache()
    {
        $bookId = (new Store)
            ->disableModelCaching()
            ->with("books")
            ->first()
            ->books
            ->first()
            ->id;
        $key = sha1("genealabs:laravel-model-caching:testing::memory::book-store:genealabslaravelmodelcachingcachedbelongstomany-book_store.book_id_=_{$bookId}");
        $tags = [
            'genealabs:laravel-model-caching:testing::memory::genealabslaravelmodelcachingtestsfixturesuncachedstore',
        ];

        $result = (new Book)
            ->find($bookId)
            ->uncachedStores;
        $cachedResult = $this
            ->cache()
            ->tags($tags)
            ->get($key)['value'];
        $uncachedResult = (new UncachedBook)
            ->find($bookId)
            ->stores;

        $this->assertEquals($uncachedResult->pluck("id"), $result->pluck("id"));
        $this->assertNull($cachedResult);
        $this->assertNotNull($result);
        $this->assertNotNull($uncachedResult);
    }
}