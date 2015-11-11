<?php

use Mockery as m;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class DatabaseEloquentModelTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();

        Illuminate\Database\Eloquent\Model::unsetEventDispatcher();
        Carbon\Carbon::resetToStringFormat();
    }

    public function testAttributeManipulation()
    {
        $model = new EloquentModelStub;
        $model->name = 'foo';
        $this->assertEquals('foo', $model->name);
        $this->assertTrue(isset($model->name));
        unset($model->name);
        $this->assertFalse(isset($model->name));

        // test mutation
        $model->list_items = ['name' => 'taylor'];
        $this->assertEquals(['name' => 'taylor'], $model->list_items);
        $attributes = $model->getAttributes();
        $this->assertEquals(json_encode(['name' => 'taylor']), $attributes['list_items']);
    }

    public function testDirtyAttributes()
    {
        $model = new EloquentModelStub(['foo' => '1', 'bar' => 2, 'baz' => 3]);
        $model->syncOriginal();
        $model->foo = 1;
        $model->bar = 20;
        $model->baz = 30;

        $this->assertTrue($model->isDirty());
        $this->assertFalse($model->isDirty('foo'));
        $this->assertTrue($model->isDirty('bar'));
        $this->assertTrue($model->isDirty('foo', 'bar'));
        $this->assertTrue($model->isDirty(['foo', 'bar']));
    }

    public function testCalculatedAttributes()
    {
        $model = new EloquentModelStub;
        $model->password = 'secret';
        $attributes = $model->getAttributes();

        // ensure password attribute was not set to null
        $this->assertArrayNotHasKey('password', $attributes);
        $this->assertEquals('******', $model->password);
        $this->assertEquals('5ebe2294ecd0e0f08eab7690d2a6ee69', $attributes['password_hash']);
        $this->assertEquals('5ebe2294ecd0e0f08eab7690d2a6ee69', $model->password_hash);
    }

    public function testNewInstanceReturnsNewInstanceWithAttributesSet()
    {
        $model = new EloquentModelStub;
        $instance = $model->newInstance(['name' => 'taylor']);
        $this->assertInstanceOf('EloquentModelStub', $instance);
        $this->assertEquals('taylor', $instance->name);
    }

    public function testHydrateCreatesCollectionOfModels()
    {
        $data = [['name' => 'Taylor'], ['name' => 'Otwell']];
        $collection = EloquentModelStub::hydrate($data, 'foo_connection');

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $collection);
        $this->assertCount(2, $collection);
        $this->assertInstanceOf('EloquentModelStub', $collection[0]);
        $this->assertInstanceOf('EloquentModelStub', $collection[1]);
        $this->assertEquals($collection[0]->getAttributes(), $collection[0]->getOriginal());
        $this->assertEquals($collection[1]->getAttributes(), $collection[1]->getOriginal());
        $this->assertEquals('Taylor', $collection[0]->name);
        $this->assertEquals('Otwell', $collection[1]->name);
        $this->assertEquals('foo_connection', $collection[0]->getConnectionName());
        $this->assertEquals('foo_connection', $collection[1]->getConnectionName());
    }

    public function testHydrateRawMakesRawQuery()
    {
        $collection = EloquentModelHydrateRawStub::hydrateRaw('SELECT ?', ['foo']);
        $this->assertEquals('hydrated', $collection);
    }

    public function testCreateMethodSavesNewModel()
    {
        $_SERVER['__eloquent.saved'] = false;
        $model = EloquentModelSaveStub::create(['name' => 'taylor']);
        $this->assertTrue($_SERVER['__eloquent.saved']);
        $this->assertEquals('taylor', $model->name);
    }

    public function testForceCreateMethodSavesNewModelWithGuardedAttributes()
    {
        $_SERVER['__eloquent.saved'] = false;
        $model = EloquentModelSaveStub::forceCreate(['id' => 21]);
        $this->assertTrue($_SERVER['__eloquent.saved']);
        $this->assertEquals(21, $model->id);
    }

    public function testFindMethodUseWritePdo()
    {
        $result = EloquentModelFindWithWritePdoStub::onWriteConnection()->find(1);
    }

    public function testDestroyMethodCallsQueryBuilderCorrectly()
    {
        $result = EloquentModelDestroyStub::destroy(1, 2, 3);
    }

    public function testWithMethodCallsQueryBuilderCorrectly()
    {
        $result = EloquentModelWithStub::with('foo', 'bar');
        $this->assertEquals('foo', $result);
    }

    public function testWithMethodCallsQueryBuilderCorrectlyWithArray()
    {
        $result = EloquentModelWithStub::with(['foo', 'bar']);
        $this->assertEquals('foo', $result);
    }

    public function testUpdateProcess()
    {
        $model = $this->getMock('EloquentModelStub', ['newQueryWithoutScopes', 'updateTimestamps']);
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $query->shouldReceive('where')->once()->with('id', '=', 1);
        $query->shouldReceive('update')->once()->with(['name' => 'taylor'])->andReturn(1);
        $model->expects($this->once())->method('newQueryWithoutScopes')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');
        $model->setEventDispatcher($events = m::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('until')->once()->with('eloquent.saving: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('until')->once()->with('eloquent.updating: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('fire')->once()->with('eloquent.updated: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('fire')->once()->with('eloquent.saved: '.get_class($model), $model)->andReturn(true);

        $model->id = 1;
        $model->foo = 'bar';
        // make sure foo isn't synced so we can test that dirty attributes only are updated
        $model->syncOriginal();
        $model->name = 'taylor';
        $model->exists = true;
        $this->assertTrue($model->save());
    }

    public function testUpdateProcessDoesntOverrideTimestamps()
    {
        $model = $this->getMock('EloquentModelStub', ['newQueryWithoutScopes']);
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $query->shouldReceive('where')->once()->with('id', '=', 1);
        $query->shouldReceive('update')->once()->with(['created_at' => 'foo', 'updated_at' => 'bar'])->andReturn(1);
        $model->expects($this->once())->method('newQueryWithoutScopes')->will($this->returnValue($query));
        $model->setEventDispatcher($events = m::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('until');
        $events->shouldReceive('fire');

        $model->id = 1;
        $model->syncOriginal();
        $model->created_at = 'foo';
        $model->updated_at = 'bar';
        $model->exists = true;
        $this->assertTrue($model->save());
    }

    public function testSaveIsCancelledIfSavingEventReturnsFalse()
    {
        $model = $this->getMock('EloquentModelStub', ['newQueryWithoutScopes']);
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $model->expects($this->once())->method('newQueryWithoutScopes')->will($this->returnValue($query));
        $model->setEventDispatcher($events = m::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('until')->once()->with('eloquent.saving: '.get_class($model), $model)->andReturn(false);
        $model->exists = true;

        $this->assertFalse($model->save());
    }

    public function testUpdateIsCancelledIfUpdatingEventReturnsFalse()
    {
        $model = $this->getMock('EloquentModelStub', ['newQueryWithoutScopes']);
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $model->expects($this->once())->method('newQueryWithoutScopes')->will($this->returnValue($query));
        $model->setEventDispatcher($events = m::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('until')->once()->with('eloquent.saving: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('until')->once()->with('eloquent.updating: '.get_class($model), $model)->andReturn(false);
        $model->exists = true;
        $model->foo = 'bar';

        $this->assertFalse($model->save());
    }

    public function testUpdateProcessWithoutTimestamps()
    {
        $model = $this->getMock('EloquentModelStub', ['newQueryWithoutScopes', 'updateTimestamps', 'fireModelEvent']);
        $model->timestamps = false;
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $query->shouldReceive('where')->once()->with('id', '=', 1);
        $query->shouldReceive('update')->once()->with(['name' => 'taylor'])->andReturn(1);
        $model->expects($this->once())->method('newQueryWithoutScopes')->will($this->returnValue($query));
        $model->expects($this->never())->method('updateTimestamps');
        $model->expects($this->any())->method('fireModelEvent')->will($this->returnValue(true));

        $model->id = 1;
        $model->syncOriginal();
        $model->name = 'taylor';
        $model->exists = true;
        $this->assertTrue($model->save());
    }

    public function testUpdateUsesOldPrimaryKey()
    {
        $model = $this->getMock('EloquentModelStub', ['newQueryWithoutScopes', 'updateTimestamps']);
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $query->shouldReceive('where')->once()->with('id', '=', 1);
        $query->shouldReceive('update')->once()->with(['id' => 2, 'foo' => 'bar'])->andReturn(1);
        $model->expects($this->once())->method('newQueryWithoutScopes')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');
        $model->setEventDispatcher($events = m::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('until')->once()->with('eloquent.saving: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('until')->once()->with('eloquent.updating: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('fire')->once()->with('eloquent.updated: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('fire')->once()->with('eloquent.saved: '.get_class($model), $model)->andReturn(true);

        $model->id = 1;
        $model->syncOriginal();
        $model->id = 2;
        $model->foo = 'bar';
        $model->exists = true;

        $this->assertTrue($model->save());
    }

    public function testTimestampsAreReturnedAsObjects()
    {
        $model = $this->getMock('EloquentDateModelStub', ['getDateFormat']);
        $model->expects($this->any())->method('getDateFormat')->will($this->returnValue('Y-m-d'));
        $model->setRawAttributes([
            'created_at'    => '2012-12-04',
            'updated_at'    => '2012-12-05',
        ]);

        $this->assertInstanceOf('Carbon\Carbon', $model->created_at);
        $this->assertInstanceOf('Carbon\Carbon', $model->updated_at);
    }

    public function testTimestampsAreReturnedAsObjectsFromPlainDatesAndTimestamps()
    {
        $model = $this->getMock('EloquentDateModelStub', ['getDateFormat']);
        $model->expects($this->any())->method('getDateFormat')->will($this->returnValue('Y-m-d H:i:s'));
        $model->setRawAttributes([
            'created_at'    => '2012-12-04',
            'updated_at'    => time(),
        ]);

        $this->assertInstanceOf('Carbon\Carbon', $model->created_at);
        $this->assertInstanceOf('Carbon\Carbon', $model->updated_at);
    }

    public function testTimestampsAreReturnedAsObjectsOnCreate()
    {
        $timestamps = [
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ];
        $model = new EloquentDateModelStub;
        Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver = m::mock('Illuminate\Database\ConnectionResolverInterface'));
        $resolver->shouldReceive('connection')->andReturn($mockConnection = m::mock('StdClass'));
        $mockConnection->shouldReceive('getQueryGrammar')->andReturn($mockConnection);
        $mockConnection->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $instance = $model->newInstance($timestamps);
        $this->assertInstanceOf('Carbon\Carbon', $instance->updated_at);
        $this->assertInstanceOf('Carbon\Carbon', $instance->created_at);
    }

    public function testDateTimeAttributesReturnNullIfSetToNull()
    {
        $timestamps = [
            'created_at' => Carbon\Carbon::now(),
            'updated_at' => Carbon\Carbon::now(),
        ];
        $model = new EloquentDateModelStub;
        Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver = m::mock('Illuminate\Database\ConnectionResolverInterface'));
        $resolver->shouldReceive('connection')->andReturn($mockConnection = m::mock('StdClass'));
        $mockConnection->shouldReceive('getQueryGrammar')->andReturn($mockConnection);
        $mockConnection->shouldReceive('getDateFormat')->andReturn('Y-m-d H:i:s');
        $instance = $model->newInstance($timestamps);

        $instance->created_at = null;
        $this->assertNull($instance->created_at);
    }

    public function testTimestampsAreCreatedFromStringsAndIntegers()
    {
        $model = new EloquentDateModelStub;
        $model->created_at = '2013-05-22 00:00:00';
        $this->assertInstanceOf('Carbon\Carbon', $model->created_at);

        $model = new EloquentDateModelStub;
        $model->created_at = time();
        $this->assertInstanceOf('Carbon\Carbon', $model->created_at);

        $model = new EloquentDateModelStub;
        $model->created_at = 0;
        $this->assertInstanceOf('Carbon\Carbon', $model->created_at);

        $model = new EloquentDateModelStub;
        $model->created_at = '2012-01-01';
        $this->assertInstanceOf('Carbon\Carbon', $model->created_at);
    }

    public function testFromDateTime()
    {
        $model = new EloquentModelStub();

        $value = Carbon\Carbon::parse('2015-04-17 22:59:01');
        $this->assertEquals('2015-04-17 22:59:01', $model->fromDateTime($value));

        $value = new DateTime('2015-04-17 22:59:01');
        $this->assertEquals('2015-04-17 22:59:01', $model->fromDateTime($value));

        $value = '2015-04-17 22:59:01';
        $this->assertEquals('2015-04-17 22:59:01', $model->fromDateTime($value));

        $value = '2015-04-17';
        $this->assertEquals('2015-04-17 00:00:00', $model->fromDateTime($value));

        $value = '1429311541';
        $this->assertEquals('2015-04-17 22:59:01', $model->fromDateTime($value));
    }

    public function testInsertProcess()
    {
        $model = $this->getMock('EloquentModelStub', ['newQueryWithoutScopes', 'updateTimestamps', 'refresh']);
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $query->shouldReceive('insertGetId')->once()->with(['name' => 'taylor'], 'id')->andReturn(1);
        $model->expects($this->once())->method('newQueryWithoutScopes')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');

        $model->setEventDispatcher($events = m::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('until')->once()->with('eloquent.saving: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('until')->once()->with('eloquent.creating: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('fire')->once()->with('eloquent.created: '.get_class($model), $model);
        $events->shouldReceive('fire')->once()->with('eloquent.saved: '.get_class($model), $model);

        $model->name = 'taylor';
        $model->exists = false;
        $this->assertTrue($model->save());
        $this->assertEquals(1, $model->id);
        $this->assertTrue($model->exists);

        $model = $this->getMock('EloquentModelStub', ['newQueryWithoutScopes', 'updateTimestamps', 'refresh']);
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $query->shouldReceive('insert')->once()->with(['name' => 'taylor']);
        $model->expects($this->once())->method('newQueryWithoutScopes')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');
        $model->setIncrementing(false);

        $model->setEventDispatcher($events = m::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('until')->once()->with('eloquent.saving: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('until')->once()->with('eloquent.creating: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('fire')->once()->with('eloquent.created: '.get_class($model), $model);
        $events->shouldReceive('fire')->once()->with('eloquent.saved: '.get_class($model), $model);

        $model->name = 'taylor';
        $model->exists = false;
        $this->assertTrue($model->save());
        $this->assertNull($model->id);
        $this->assertTrue($model->exists);
    }

    public function testInsertIsCancelledIfCreatingEventReturnsFalse()
    {
        $model = $this->getMock('EloquentModelStub', ['newQueryWithoutScopes']);
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $model->expects($this->once())->method('newQueryWithoutScopes')->will($this->returnValue($query));
        $model->setEventDispatcher($events = m::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('until')->once()->with('eloquent.saving: '.get_class($model), $model)->andReturn(true);
        $events->shouldReceive('until')->once()->with('eloquent.creating: '.get_class($model), $model)->andReturn(false);

        $this->assertFalse($model->save());
        $this->assertFalse($model->exists);
    }

    public function testDeleteProperlyDeletesModel()
    {
        $model = $this->getMock('Illuminate\Database\Eloquent\Model', ['newQueryWithoutScopes', 'updateTimestamps', 'touchOwners']);
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $query->shouldReceive('where')->once()->with('id', '=', 1)->andReturn($query);
        $query->shouldReceive('delete')->once();
        $model->expects($this->once())->method('newQueryWithoutScopes')->will($this->returnValue($query));
        $model->expects($this->once())->method('touchOwners');
        $model->exists = true;
        $model->id = 1;
        $model->delete();
    }

    public function testPushNoRelations()
    {
        $model = $this->getMock('EloquentModelStub', ['newQueryWithoutScopes', 'updateTimestamps', 'refresh']);
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $query->shouldReceive('insertGetId')->once()->with(['name' => 'taylor'], 'id')->andReturn(1);
        $model->expects($this->once())->method('newQueryWithoutScopes')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');

        $model->name = 'taylor';
        $model->exists = false;

        $this->assertTrue($model->push());
        $this->assertEquals(1, $model->id);
        $this->assertTrue($model->exists);
    }

    public function testPushEmptyOneRelation()
    {
        $model = $this->getMock('EloquentModelStub', ['newQueryWithoutScopes', 'updateTimestamps', 'refresh']);
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $query->shouldReceive('insertGetId')->once()->with(['name' => 'taylor'], 'id')->andReturn(1);
        $model->expects($this->once())->method('newQueryWithoutScopes')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');

        $model->name = 'taylor';
        $model->exists = false;
        $model->setRelation('relationOne', null);

        $this->assertTrue($model->push());
        $this->assertEquals(1, $model->id);
        $this->assertTrue($model->exists);
        $this->assertNull($model->relationOne);
    }

    public function testPushOneRelation()
    {
        $related1 = $this->getMock('EloquentModelStub', ['newQueryWithoutScopes', 'updateTimestamps', 'refresh']);
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $query->shouldReceive('insertGetId')->once()->with(['name' => 'related1'], 'id')->andReturn(2);
        $related1->expects($this->once())->method('newQueryWithoutScopes')->will($this->returnValue($query));
        $related1->expects($this->once())->method('updateTimestamps');
        $related1->name = 'related1';
        $related1->exists = false;

        $model = $this->getMock('EloquentModelStub', ['newQueryWithoutScopes', 'updateTimestamps', 'refresh']);
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $query->shouldReceive('insertGetId')->once()->with(['name' => 'taylor'], 'id')->andReturn(1);
        $model->expects($this->once())->method('newQueryWithoutScopes')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');

        $model->name = 'taylor';
        $model->exists = false;
        $model->setRelation('relationOne', $related1);

        $this->assertTrue($model->push());
        $this->assertEquals(1, $model->id);
        $this->assertTrue($model->exists);
        $this->assertEquals(2, $model->relationOne->id);
        $this->assertTrue($model->relationOne->exists);
        $this->assertEquals(2, $related1->id);
        $this->assertTrue($related1->exists);
    }

    public function testPushEmptyManyRelation()
    {
        $model = $this->getMock('EloquentModelStub', ['newQueryWithoutScopes', 'updateTimestamps', 'refresh']);
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $query->shouldReceive('insertGetId')->once()->with(['name' => 'taylor'], 'id')->andReturn(1);
        $model->expects($this->once())->method('newQueryWithoutScopes')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');

        $model->name = 'taylor';
        $model->exists = false;
        $model->setRelation('relationMany', new Illuminate\Database\Eloquent\Collection([]));

        $this->assertTrue($model->push());
        $this->assertEquals(1, $model->id);
        $this->assertTrue($model->exists);
        $this->assertEquals(0, count($model->relationMany));
    }

    public function testPushManyRelation()
    {
        $related1 = $this->getMock('EloquentModelStub', ['newQueryWithoutScopes', 'updateTimestamps', 'refresh']);
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $query->shouldReceive('insertGetId')->once()->with(['name' => 'related1'], 'id')->andReturn(2);
        $related1->expects($this->once())->method('newQueryWithoutScopes')->will($this->returnValue($query));
        $related1->expects($this->once())->method('updateTimestamps');
        $related1->name = 'related1';
        $related1->exists = false;

        $related2 = $this->getMock('EloquentModelStub', ['newQueryWithoutScopes', 'updateTimestamps', 'refresh']);
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $query->shouldReceive('insertGetId')->once()->with(['name' => 'related2'], 'id')->andReturn(3);
        $related2->expects($this->once())->method('newQueryWithoutScopes')->will($this->returnValue($query));
        $related2->expects($this->once())->method('updateTimestamps');
        $related2->name = 'related2';
        $related2->exists = false;

        $model = $this->getMock('EloquentModelStub', ['newQueryWithoutScopes', 'updateTimestamps', 'refresh']);
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $query->shouldReceive('insertGetId')->once()->with(['name' => 'taylor'], 'id')->andReturn(1);
        $model->expects($this->once())->method('newQueryWithoutScopes')->will($this->returnValue($query));
        $model->expects($this->once())->method('updateTimestamps');

        $model->name = 'taylor';
        $model->exists = false;
        $model->setRelation('relationMany', new Illuminate\Database\Eloquent\Collection([$related1, $related2]));

        $this->assertTrue($model->push());
        $this->assertEquals(1, $model->id);
        $this->assertTrue($model->exists);
        $this->assertEquals(2, count($model->relationMany));
        $this->assertEquals([2, 3], $model->relationMany->pluck('id')->all());
    }

    public function testNewQueryReturnsEloquentQueryBuilder()
    {
        $conn = m::mock('Illuminate\Database\Connection');
        $grammar = m::mock('Illuminate\Database\Query\Grammars\Grammar');
        $processor = m::mock('Illuminate\Database\Query\Processors\Processor');
        $conn->shouldReceive('getQueryGrammar')->once()->andReturn($grammar);
        $conn->shouldReceive('getPostProcessor')->once()->andReturn($processor);
        EloquentModelStub::setConnectionResolver($resolver = m::mock('Illuminate\Database\ConnectionResolverInterface'));
        $resolver->shouldReceive('connection')->andReturn($conn);
        $model = new EloquentModelStub;
        $builder = $model->newQuery();
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Builder', $builder);
    }

    public function testGetAndSetTableOperations()
    {
        $model = new EloquentModelStub;
        $this->assertEquals('stub', $model->getTable());
        $model->setTable('foo');
        $this->assertEquals('foo', $model->getTable());
    }

    public function testGetKeyReturnsValueOfPrimaryKey()
    {
        $model = new EloquentModelStub;
        $model->id = 1;
        $this->assertEquals(1, $model->getKey());
        $this->assertEquals('id', $model->getKeyName());
    }

    public function testConnectionManagement()
    {
        EloquentModelStub::setConnectionResolver($resolver = m::mock('Illuminate\Database\ConnectionResolverInterface'));
        $model = new EloquentModelStub;
        $model->setConnection('foo');
        $resolver->shouldReceive('connection')->once()->with('foo')->andReturn('bar');

        $this->assertEquals('bar', $model->getConnection());
    }

    public function testToArray()
    {
        $model = new EloquentModelStub;
        $model->name = 'foo';
        $model->age = null;
        $model->password = 'password1';
        $model->setHidden(['password']);
        $model->setRelation('names', new Illuminate\Database\Eloquent\Collection([
            new EloquentModelStub(['bar' => 'baz']), new EloquentModelStub(['bam' => 'boom']),
        ]));
        $model->setRelation('partner', new EloquentModelStub(['name' => 'abby']));
        $model->setRelation('group', null);
        $model->setRelation('multi', new Illuminate\Database\Eloquent\Collection);
        $array = $model->toArray();

        $this->assertInternalType('array', $array);
        $this->assertEquals('foo', $array['name']);
        $this->assertEquals('baz', $array['names'][0]['bar']);
        $this->assertEquals('boom', $array['names'][1]['bam']);
        $this->assertEquals('abby', $array['partner']['name']);
        $this->assertNull($array['group']);
        $this->assertEquals([], $array['multi']);
        $this->assertFalse(isset($array['password']));

        $model->setAppends(['appendable']);
        $array = $model->toArray();
        $this->assertEquals('appended', $array['appendable']);
    }

    public function testVisibleCreatesArrayWhitelist()
    {
        $model = new EloquentModelStub;
        $model->setVisible(['name']);
        $model->name = 'Taylor';
        $model->age = 26;
        $array = $model->toArray();

        $this->assertEquals(['name' => 'Taylor'], $array);
    }

    public function testHiddenCanAlsoExcludeRelationships()
    {
        $model = new EloquentModelStub;
        $model->name = 'Taylor';
        $model->setRelation('foo', ['bar']);
        $model->setHidden(['foo', 'list_items', 'password']);
        $array = $model->toArray();

        $this->assertEquals(['name' => 'Taylor'], $array);
    }

    public function testGetArrayableRelationsFunctionExcludeHiddenRelationships()
    {
        $model = new EloquentModelStub;

        $class = new ReflectionClass($model);
        $method = $class->getMethod('getArrayableRelations');
        $method->setAccessible(true);

        $model->setRelation('foo', ['bar']);
        $model->setRelation('bam', ['boom']);
        $model->setHidden(['foo']);

        $array = $method->invokeArgs($model, []);

        $this->assertSame(['bam' => ['boom']], $array);
    }

    public function testToArraySnakeAttributes()
    {
        $model = new EloquentModelStub;
        $model->setRelation('namesList', new Illuminate\Database\Eloquent\Collection([
            new EloquentModelStub(['bar' => 'baz']), new EloquentModelStub(['bam' => 'boom']),
        ]));
        $array = $model->toArray();

        $this->assertEquals('baz', $array['names_list'][0]['bar']);
        $this->assertEquals('boom', $array['names_list'][1]['bam']);

        $model = new EloquentModelCamelStub;
        $model->setRelation('namesList', new Illuminate\Database\Eloquent\Collection([
            new EloquentModelStub(['bar' => 'baz']), new EloquentModelStub(['bam' => 'boom']),
        ]));
        $array = $model->toArray();

        $this->assertEquals('baz', $array['namesList'][0]['bar']);
        $this->assertEquals('boom', $array['namesList'][1]['bam']);
    }

    public function testToArrayUsesMutators()
    {
        $model = new EloquentModelStub;
        $model->list_items = [1, 2, 3];
        $array = $model->toArray();

        $this->assertEquals([1, 2, 3], $array['list_items']);
    }

    public function testHidden()
    {
        $model = new EloquentModelStub(['name' => 'foo', 'age' => 'bar', 'id' => 'baz']);
        $model->setHidden(['age', 'id']);
        $array = $model->toArray();
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('age', $array);
    }

    public function testVisible()
    {
        $model = new EloquentModelStub(['name' => 'foo', 'age' => 'bar', 'id' => 'baz']);
        $model->setVisible(['name', 'id']);
        $array = $model->toArray();
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('age', $array);
    }

    public function testHiddenAreIgnoringWhenVisibleExists()
    {
        $model = new EloquentModelStub(['name' => 'foo', 'age' => 'bar', 'id' => 'baz']);
        $model->setVisible(['name', 'id']);
        $model->setHidden(['name', 'age']);
        $array = $model->toArray();
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayNotHasKey('age', $array);
    }

    public function testDynamicHidden()
    {
        $model = new EloquentModelDynamicHiddenStub(['name' => 'foo', 'age' => 'bar', 'id' => 'baz']);
        $array = $model->toArray();
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('age', $array);
    }

    public function testWithHidden()
    {
        $model = new EloquentModelStub(['name' => 'foo', 'age' => 'bar', 'id' => 'baz']);
        $model->setHidden(['age', 'id']);
        $model->withHidden('age');
        $array = $model->toArray();
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('age', $array);
        $this->assertArrayNotHasKey('id', $array);
    }

    public function testDynamicVisible()
    {
        $model = new EloquentModelDynamicVisibleStub(['name' => 'foo', 'age' => 'bar', 'id' => 'baz']);
        $array = $model->toArray();
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('age', $array);
    }

    public function testFillable()
    {
        $model = new EloquentModelStub;
        $model->fillable(['name', 'age']);
        $model->fill(['name' => 'foo', 'age' => 'bar']);
        $this->assertEquals('foo', $model->name);
        $this->assertEquals('bar', $model->age);
    }

    public function testForceFillMethodFillsGuardedAttributes()
    {
        $model = (new EloquentModelSaveStub)->forceFill(['id' => 21]);
        $this->assertEquals(21, $model->id);
    }

    public function testUnguardAllowsAnythingToBeSet()
    {
        $model = new EloquentModelStub;
        EloquentModelStub::unguard();
        $model->guard(['*']);
        $model->fill(['name' => 'foo', 'age' => 'bar']);
        $this->assertEquals('foo', $model->name);
        $this->assertEquals('bar', $model->age);
        EloquentModelStub::unguard(false);
    }

    public function testUnderscorePropertiesAreNotFilled()
    {
        $model = new EloquentModelStub;
        $model->fill(['_method' => 'PUT']);
        $this->assertEquals([], $model->getAttributes());
    }

    public function testGuarded()
    {
        $model = new EloquentModelStub;
        $model->guard(['name', 'age']);
        $model->fill(['name' => 'foo', 'age' => 'bar', 'foo' => 'bar']);
        $this->assertFalse(isset($model->name));
        $this->assertFalse(isset($model->age));
        $this->assertEquals('bar', $model->foo);
    }

    public function testFillableOverridesGuarded()
    {
        $model = new EloquentModelStub;
        $model->guard(['name', 'age']);
        $model->fillable(['age', 'foo']);
        $model->fill(['name' => 'foo', 'age' => 'bar', 'foo' => 'bar']);
        $this->assertFalse(isset($model->name));
        $this->assertEquals('bar', $model->age);
        $this->assertEquals('bar', $model->foo);
    }

    /**
     * @expectedException Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function testGlobalGuarded()
    {
        $model = new EloquentModelStub;
        $model->guard(['*']);
        $model->fill(['name' => 'foo', 'age' => 'bar', 'votes' => 'baz']);
    }

    public function testUnguardedRunsCallbackWhileBeingUnguarded()
    {
        $model = Model::unguarded(function () {
            return (new EloquentModelStub)->guard(['*'])->fill(['name' => 'Taylor']);
        });
        $this->assertEquals('Taylor', $model->name);
        $this->assertFalse(Model::isUnguarded());
    }

    public function testUnguardedCallDoesNotChangeUnguardedState()
    {
        Model::unguard();
        $model = Model::unguarded(function () {
            return (new EloquentModelStub)->guard(['*'])->fill(['name' => 'Taylor']);
        });
        $this->assertEquals('Taylor', $model->name);
        $this->assertTrue(Model::isUnguarded());
        Model::reguard();
    }

    public function testHasOneCreatesProperRelation()
    {
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->hasOne('EloquentModelSaveStub');
        $this->assertEquals('save_stub.eloquent_model_stub_id', $relation->getForeignKey());

        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->hasOne('EloquentModelSaveStub', 'foo');
        $this->assertEquals('save_stub.foo', $relation->getForeignKey());
        $this->assertSame($model, $relation->getParent());
        $this->assertInstanceOf('EloquentModelSaveStub', $relation->getQuery()->getModel());
    }

    public function testMorphOneCreatesProperRelation()
    {
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->morphOne('EloquentModelSaveStub', 'morph');
        $this->assertEquals('save_stub.morph_id', $relation->getForeignKey());
        $this->assertEquals('save_stub.morph_type', $relation->getMorphType());
        $this->assertEquals('EloquentModelStub', $relation->getMorphClass());
    }

    public function testCorrectMorphClassIsReturned()
    {
        Relation::morphMap(['alias' => 'AnotherModel']);
        $model = new EloquentModelStub;

        try {
            $this->assertEquals('EloquentModelStub', $model->getMorphClass());
        } finally {
            Relation::morphMap([], false);
        }
    }

    public function testHasManyCreatesProperRelation()
    {
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->hasMany('EloquentModelSaveStub');
        $this->assertEquals('save_stub.eloquent_model_stub_id', $relation->getForeignKey());

        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->hasMany('EloquentModelSaveStub', 'foo');
        $this->assertEquals('save_stub.foo', $relation->getForeignKey());
        $this->assertSame($model, $relation->getParent());
        $this->assertInstanceOf('EloquentModelSaveStub', $relation->getQuery()->getModel());
    }

    public function testMorphManyCreatesProperRelation()
    {
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->morphMany('EloquentModelSaveStub', 'morph');
        $this->assertEquals('save_stub.morph_id', $relation->getForeignKey());
        $this->assertEquals('save_stub.morph_type', $relation->getMorphType());
        $this->assertEquals('EloquentModelStub', $relation->getMorphClass());
    }

    public function testBelongsToCreatesProperRelation()
    {
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->belongsToStub();
        $this->assertEquals('belongs_to_stub_id', $relation->getForeignKey());
        $this->assertSame($model, $relation->getParent());
        $this->assertInstanceOf('EloquentModelSaveStub', $relation->getQuery()->getModel());

        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->belongsToExplicitKeyStub();
        $this->assertEquals('foo', $relation->getForeignKey());
    }

    public function testMorphToCreatesProperRelation()
    {
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->morphToStub();
        $this->assertEquals('morph_to_stub_id', $relation->getForeignKey());
        $this->assertSame($model, $relation->getParent());
        $this->assertInstanceOf('EloquentModelSaveStub', $relation->getQuery()->getModel());
    }

    public function testBelongsToManyCreatesProperRelation()
    {
        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->belongsToMany('EloquentModelSaveStub');
        $this->assertEquals('eloquent_model_save_stub_eloquent_model_stub.eloquent_model_stub_id', $relation->getForeignKey());
        $this->assertEquals('eloquent_model_save_stub_eloquent_model_stub.eloquent_model_save_stub_id', $relation->getOtherKey());
        $this->assertSame($model, $relation->getParent());
        $this->assertInstanceOf('EloquentModelSaveStub', $relation->getQuery()->getModel());
        $this->assertEquals(__FUNCTION__, $relation->getRelationName());

        $model = new EloquentModelStub;
        $this->addMockConnection($model);
        $relation = $model->belongsToMany('EloquentModelSaveStub', 'table', 'foreign', 'other');
        $this->assertEquals('table.foreign', $relation->getForeignKey());
        $this->assertEquals('table.other', $relation->getOtherKey());
        $this->assertSame($model, $relation->getParent());
        $this->assertInstanceOf('EloquentModelSaveStub', $relation->getQuery()->getModel());
    }

    public function testModelsAssumeTheirName()
    {
        $model = new EloquentModelWithoutTableStub;
        $this->assertEquals('eloquent_model_without_table_stubs', $model->getTable());

        require_once __DIR__.'/stubs/EloquentModelNamespacedStub.php';
        $namespacedModel = new Foo\Bar\EloquentModelNamespacedStub;
        $this->assertEquals('eloquent_model_namespaced_stubs', $namespacedModel->getTable());
    }

    public function testTheMutatorCacheIsPopulated()
    {
        $class = new EloquentModelStub;

        $expectedAttributes = [
            'list_items',
            'password',
            'appendable',
        ];

        $this->assertEquals($expectedAttributes, $class->getMutatedAttributes());
    }

    public function testRouteKeyIsPrimaryKey()
    {
        $model = new EloquentModelStub;
        $model->id = 'foo';
        $this->assertEquals('foo', $model->getRouteKey());
    }

    public function testRouteNameIsPrimaryKeyName()
    {
        $model = new EloquentModelStub;
        $this->assertEquals('id', $model->getRouteKeyName());
    }

    public function testCloneModelMakesAFreshCopyOfTheModel()
    {
        $class = new EloquentModelStub;
        $class->id = 1;
        $class->exists = true;
        $class->first = 'taylor';
        $class->last = 'otwell';
        $class->created_at = $class->freshTimestamp();
        $class->updated_at = $class->freshTimestamp();
        $class->setRelation('foo', ['bar']);

        $clone = $class->replicate();

        $this->assertNull($clone->id);
        $this->assertFalse($clone->exists);
        $this->assertEquals('taylor', $clone->first);
        $this->assertEquals('otwell', $clone->last);
        $this->assertObjectNotHasAttribute('created_at', $clone);
        $this->assertObjectNotHasAttribute('updated_at', $clone);
        $this->assertEquals(['bar'], $clone->foo);
    }

    public function testModelObserversCanBeAttachedToModels()
    {
        EloquentModelStub::setEventDispatcher($events = m::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('listen')->once()->with('eloquent.creating: EloquentModelStub', 'EloquentTestObserverStub@creating', 0);
        $events->shouldReceive('listen')->once()->with('eloquent.saved: EloquentModelStub', 'EloquentTestObserverStub@saved', 0);
        $events->shouldReceive('forget');
        EloquentModelStub::observe(new EloquentTestObserverStub);
        EloquentModelStub::flushEventListeners();
    }

    public function testModelObserversCanBeAttachedToModelsWithString()
    {
        EloquentModelStub::setEventDispatcher($events = m::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('listen')->once()->with('eloquent.creating: EloquentModelStub', 'EloquentTestObserverStub@creating', 0);
        $events->shouldReceive('listen')->once()->with('eloquent.saved: EloquentModelStub', 'EloquentTestObserverStub@saved', 0);
        $events->shouldReceive('forget');
        EloquentModelStub::observe('EloquentTestObserverStub');
        EloquentModelStub::flushEventListeners();
    }

    public function testSetObservableEvents()
    {
        $class = new EloquentModelStub;
        $class->setObservableEvents(['foo']);

        $this->assertContains('foo', $class->getObservableEvents());
    }

    public function testAddObservableEvent()
    {
        $class = new EloquentModelStub;
        $class->addObservableEvents('foo');

        $this->assertContains('foo', $class->getObservableEvents());
    }

    public function testAddMultipleObserveableEvents()
    {
        $class = new EloquentModelStub;
        $class->addObservableEvents('foo', 'bar');

        $this->assertContains('foo', $class->getObservableEvents());
        $this->assertContains('bar', $class->getObservableEvents());
    }

    public function testRemoveObservableEvent()
    {
        $class = new EloquentModelStub;
        $class->setObservableEvents(['foo', 'bar']);
        $class->removeObservableEvents('bar');

        $this->assertNotContains('bar', $class->getObservableEvents());
    }

    public function testRemoveMultipleObservableEvents()
    {
        $class = new EloquentModelStub;
        $class->setObservableEvents(['foo', 'bar']);
        $class->removeObservableEvents('foo', 'bar');

        $this->assertNotContains('foo', $class->getObservableEvents());
        $this->assertNotContains('bar', $class->getObservableEvents());
    }

    /**
     * @expectedException LogicException
     */
    public function testGetModelAttributeMethodThrowsExceptionIfNotRelation()
    {
        $model = new EloquentModelStub;
        $relation = $model->incorrectRelationStub;
    }

    public function testModelIsBootedOnUnserialize()
    {
        $model = new EloquentModelBootingTestStub;
        $this->assertTrue(EloquentModelBootingTestStub::isBooted());
        $model->foo = 'bar';
        $string = serialize($model);
        $model = null;
        EloquentModelBootingTestStub::unboot();
        $this->assertFalse(EloquentModelBootingTestStub::isBooted());
        $model = unserialize($string);
        $this->assertTrue(EloquentModelBootingTestStub::isBooted());
    }

    public function testAppendingOfAttributes()
    {
        $model = new EloquentModelAppendsStub;

        $this->assertTrue(isset($model->is_admin));
        $this->assertTrue(isset($model->camelCased));
        $this->assertTrue(isset($model->StudlyCased));

        $this->assertEquals('admin', $model->is_admin);
        $this->assertEquals('camelCased', $model->camelCased);
        $this->assertEquals('StudlyCased', $model->StudlyCased);

        $model->setHidden(['is_admin', 'camelCased', 'StudlyCased']);
        $this->assertEquals([], $model->toArray());

        $model->setVisible([]);
        $this->assertEquals([], $model->toArray());
    }

    public function testReplicateCreatesANewModelInstanceWithSameAttributeValues()
    {
        $model = new EloquentModelStub;
        $model->id = 'id';
        $model->foo = 'bar';
        $model->created_at = new DateTime;
        $model->updated_at = new DateTime;
        $replicated = $model->replicate();

        $this->assertNull($replicated->id);
        $this->assertEquals('bar', $replicated->foo);
        $this->assertNull($replicated->created_at);
        $this->assertNull($replicated->updated_at);
    }

    public function testIncrementOnExistingModelCallsQueryAndSetsAttribute()
    {
        $model = m::mock('EloquentModelStub[newQuery]');
        $model->exists = true;
        $model->id = 1;
        $model->syncOriginalAttribute('id');
        $model->foo = 2;

        $model->shouldReceive('newQuery')->andReturn($query = m::mock('StdClass'));
        $query->shouldReceive('where')->andReturn($query);
        $query->shouldReceive('increment');

        $model->publicIncrement('foo');

        $this->assertEquals(3, $model->foo);
        $this->assertFalse($model->isDirty());
    }

    public function testRelationshipTouchOwnersIsPropagated()
    {
        $relation = $this->getMockBuilder('Illuminate\Database\Eloquent\Relations\BelongsTo')->setMethods(['touch'])->disableOriginalConstructor()->getMock();
        $relation->expects($this->once())->method('touch');

        $model = m::mock('EloquentModelStub[partner]');
        $this->addMockConnection($model);
        $model->shouldReceive('partner')->once()->andReturn($relation);
        $model->setTouchedRelations(['partner']);

        $mockPartnerModel = m::mock('EloquentModelStub[touchOwners]');
        $mockPartnerModel->shouldReceive('touchOwners')->once();
        $model->setRelation('partner', $mockPartnerModel);

        $model->touchOwners();
    }

    public function testRelationshipTouchOwnersIsNotPropagatedIfNoRelationshipResult()
    {
        $relation = $this->getMockBuilder('Illuminate\Database\Eloquent\Relations\BelongsTo')->setMethods(['touch'])->disableOriginalConstructor()->getMock();
        $relation->expects($this->once())->method('touch');

        $model = m::mock('EloquentModelStub[partner]');
        $this->addMockConnection($model);
        $model->shouldReceive('partner')->once()->andReturn($relation);
        $model->setTouchedRelations(['partner']);

        $model->setRelation('partner', null);

        $model->touchOwners();
    }

    public function testTimestampsAreNotUpdatedWithTimestampsFalseSaveOption()
    {
        $model = m::mock('EloquentModelStub[newQueryWithoutScopes]');
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $query->shouldReceive('where')->once()->with('id', '=', 1);
        $query->shouldReceive('update')->once()->with(['name' => 'taylor'])->andReturn(1);
        $model->shouldReceive('newQueryWithoutScopes')->once()->andReturn($query);

        $model->id = 1;
        $model->syncOriginal();
        $model->name = 'taylor';
        $model->exists = true;
        $this->assertTrue($model->save(['timestamps' => false]));
        $this->assertNull($model->updated_at);
    }

    public function testModelAttributesAreCastedWhenPresentInCastsArray()
    {
        $model = new EloquentModelCastingStub;
        $model->setDateFormat('Y-m-d H:i:s');
        $model->first = '3';
        $model->second = '4.0';
        $model->third = 2.5;
        $model->fourth = 1;
        $model->fifth = 0;
        $model->sixth = ['foo' => 'bar'];
        $obj = new StdClass;
        $obj->foo = 'bar';
        $model->seventh = $obj;
        $model->eighth = ['foo' => 'bar'];
        $model->ninth = '1969-07-20';
        $model->tenth = '1969-07-20 22:56:00';
        $model->eleventh = '1969-07-20 22:56:00';

        $this->assertInternalType('int', $model->first);
        $this->assertInternalType('float', $model->second);
        $this->assertInternalType('string', $model->third);
        $this->assertInternalType('boolean', $model->fourth);
        $this->assertInternalType('boolean', $model->fifth);
        $this->assertInternalType('object', $model->sixth);
        $this->assertInternalType('array', $model->seventh);
        $this->assertInternalType('array', $model->eighth);
        $this->assertTrue($model->fourth);
        $this->assertFalse($model->fifth);
        $this->assertEquals($obj, $model->sixth);
        $this->assertEquals(['foo' => 'bar'], $model->seventh);
        $this->assertEquals(['foo' => 'bar'], $model->eighth);
        $this->assertEquals('{"foo":"bar"}', $model->eighthAttributeValue());
        $this->assertInstanceOf('Carbon\Carbon', $model->ninth);
        $this->assertInstanceOf('Carbon\Carbon', $model->tenth);
        $this->assertEquals('1969-07-20', $model->ninth->toDateString());
        $this->assertEquals('1969-07-20 22:56:00', $model->tenth->toDateTimeString());
        $this->assertEquals(-14173440, $model->eleventh);

        $arr = $model->toArray();
        $this->assertInternalType('int', $arr['first']);
        $this->assertInternalType('float', $arr['second']);
        $this->assertInternalType('string', $arr['third']);
        $this->assertInternalType('boolean', $arr['fourth']);
        $this->assertInternalType('boolean', $arr['fifth']);
        $this->assertInternalType('object', $arr['sixth']);
        $this->assertInternalType('array', $arr['seventh']);
        $this->assertInternalType('array', $arr['eighth']);
        $this->assertTrue($arr['fourth']);
        $this->assertFalse($arr['fifth']);
        $this->assertEquals($obj, $arr['sixth']);
        $this->assertEquals(['foo' => 'bar'], $arr['seventh']);
        $this->assertEquals(['foo' => 'bar'], $arr['eighth']);
        $this->assertInstanceOf('Carbon\Carbon', $arr['ninth']);
        $this->assertInstanceOf('Carbon\Carbon', $arr['tenth']);
        $this->assertEquals('1969-07-20', $arr['ninth']->toDateString());
        $this->assertEquals('1969-07-20 22:56:00', $arr['tenth']->toDateTimeString());
        $this->assertEquals(-14173440, $arr['eleventh']);
    }

    public function testModelAttributeCastingPreservesNull()
    {
        $model = new EloquentModelCastingStub;
        $model->first = null;
        $model->second = null;
        $model->third = null;
        $model->fourth = null;
        $model->fifth = null;
        $model->sixth = null;
        $model->seventh = null;
        $model->eighth = null;
        $model->ninth = null;
        $model->tenth = null;
        $model->eleventh = null;

        $attributes = $model->getAttributes();

        $this->assertNull($attributes['first']);
        $this->assertNull($attributes['second']);
        $this->assertNull($attributes['third']);
        $this->assertNull($attributes['fourth']);
        $this->assertNull($attributes['fifth']);
        $this->assertNull($attributes['sixth']);
        $this->assertNull($attributes['seventh']);
        $this->assertNull($attributes['eighth']);
        $this->assertNull($attributes['ninth']);
        $this->assertNull($attributes['tenth']);
        $this->assertNull($attributes['eleventh']);

        $this->assertNull($model->first);
        $this->assertNull($model->second);
        $this->assertNull($model->third);
        $this->assertNull($model->fourth);
        $this->assertNull($model->fifth);
        $this->assertNull($model->sixth);
        $this->assertNull($model->seventh);
        $this->assertNull($model->eighth);
        $this->assertNull($model->ninth);
        $this->assertNull($model->tenth);
        $this->assertNull($model->eleventh);

        $array = $model->toArray();

        $this->assertNull($array['first']);
        $this->assertNull($array['second']);
        $this->assertNull($array['third']);
        $this->assertNull($array['fourth']);
        $this->assertNull($array['fifth']);
        $this->assertNull($array['sixth']);
        $this->assertNull($array['seventh']);
        $this->assertNull($array['eighth']);
        $this->assertNull($array['ninth']);
        $this->assertNull($array['tenth']);
        $this->assertNull($array['eleventh']);
    }

    protected function addMockConnection($model)
    {
        $model->setConnectionResolver($resolver = m::mock('Illuminate\Database\ConnectionResolverInterface'));
        $resolver->shouldReceive('connection')->andReturn(m::mock('Illuminate\Database\Connection'));
        $model->getConnection()->shouldReceive('getQueryGrammar')->andReturn(m::mock('Illuminate\Database\Query\Grammars\Grammar'));
        $model->getConnection()->shouldReceive('getPostProcessor')->andReturn(m::mock('Illuminate\Database\Query\Processors\Processor'));
    }
}

class EloquentTestObserverStub
{
    public function creating()
    {
    }

    public function saved()
    {
    }
}

class EloquentModelStub extends Model
{
    protected $table = 'stub';
    protected $guarded = [];
    protected $morph_to_stub_type = 'EloquentModelSaveStub';

    public function getListItemsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setListItemsAttribute($value)
    {
        $this->attributes['list_items'] = json_encode($value);
    }

    public function getPasswordAttribute()
    {
        return '******';
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password_hash'] = md5($value);
    }

    public function publicIncrement($column, $amount = 1)
    {
        return $this->increment($column, $amount);
    }

    public function belongsToStub()
    {
        return $this->belongsTo('EloquentModelSaveStub');
    }

    public function morphToStub()
    {
        return $this->morphTo();
    }

    public function belongsToExplicitKeyStub()
    {
        return $this->belongsTo('EloquentModelSaveStub', 'foo');
    }

    public function incorrectRelationStub()
    {
        return 'foo';
    }

    public function getDates()
    {
        return [];
    }

    public function getAppendableAttribute()
    {
        return 'appended';
    }
}

class EloquentModelCamelStub extends EloquentModelStub
{
    public static $snakeAttributes = false;
}

class EloquentDateModelStub extends EloquentModelStub
{
    public function getDates()
    {
        return ['created_at', 'updated_at'];
    }
}

class EloquentModelSaveStub extends Model
{
    protected $table = 'save_stub';
    protected $guarded = ['id'];

    public function save(array $options = [])
    {
        $_SERVER['__eloquent.saved'] = true;
    }

    public function setIncrementing($value)
    {
        $this->incrementing = $value;
    }
}

class EloquentModelFindWithWritePdoStub extends Model
{
    public function newQuery()
    {
        $mock = m::mock('Illuminate\Database\Eloquent\Builder');
        $mock->shouldReceive('useWritePdo')->once()->andReturnSelf();
        $mock->shouldReceive('find')->once()->with(1)->andReturn('foo');

        return $mock;
    }
}

class EloquentModelDestroyStub extends Model
{
    public function newQuery()
    {
        $mock = m::mock('Illuminate\Database\Eloquent\Builder');
        $mock->shouldReceive('whereIn')->once()->with('id', [1, 2, 3])->andReturn($mock);
        $mock->shouldReceive('get')->once()->andReturn([$model = m::mock('StdClass')]);
        $model->shouldReceive('delete')->once();

        return $mock;
    }
}

class EloquentModelHydrateRawStub extends Model
{
    public static function hydrate(array $items, $connection = null)
    {
        return 'hydrated';
    }

    public function getConnection()
    {
        $mock = m::mock('Illuminate\Database\Connection');
        $mock->shouldReceive('select')->once()->with('SELECT ?', ['foo'])->andReturn([]);

        return $mock;
    }
}

class EloquentModelWithStub extends Model
{
    public function newQuery()
    {
        $mock = m::mock('Illuminate\Database\Eloquent\Builder');
        $mock->shouldReceive('with')->once()->with(['foo', 'bar'])->andReturn('foo');

        return $mock;
    }
}

class EloquentModelWithoutTableStub extends Model
{
}

class EloquentModelBootingTestStub extends Model
{
    public static function unboot()
    {
        unset(static::$booted[get_called_class()]);
    }

    public static function isBooted()
    {
        return array_key_exists(get_called_class(), static::$booted);
    }
}

class EloquentModelAppendsStub extends Model
{
    protected $appends = ['is_admin', 'camelCased', 'StudlyCased'];

    public function getIsAdminAttribute()
    {
        return 'admin';
    }

    public function getCamelCasedAttribute()
    {
        return 'camelCased';
    }

    public function getStudlyCasedAttribute()
    {
        return 'StudlyCased';
    }
}

class EloquentModelCastingStub extends Model
{
    protected $casts = [
        'first' => 'int',
        'second' => 'float',
        'third' => 'string',
        'fourth' => 'bool',
        'fifth' => 'boolean',
        'sixth' => 'object',
        'seventh' => 'array',
        'eighth' => 'json',
        'ninth' => 'date',
        'tenth' => 'datetime',
        'eleventh' => 'timestamp',
    ];

    public function eighthAttributeValue()
    {
        return $this->attributes['eighth'];
    }
}

class EloquentModelDynamicHiddenStub extends Illuminate\Database\Eloquent\Model
{
    protected $table = 'stub';
    protected $guarded = [];

    public function getHidden()
    {
        return ['age', 'id'];
    }
}

class EloquentModelDynamicVisibleStub extends Illuminate\Database\Eloquent\Model
{
    protected $table = 'stub';
    protected $guarded = [];

    public function getVisible()
    {
        return ['name', 'id'];
    }
}
