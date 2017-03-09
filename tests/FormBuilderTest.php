<?php

use Collective\Html\Eloquent\FormAccessible;
use Collective\Html\HtmlBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\UrlGenerator;
use Illuminate\View\Factory;
use ZhangYiJiang\ArrayRequest\FormBuilder;

class FormBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var FormBuilder
     */
    protected $formBuilder;
    
    /**
     * @var \Illuminate\Session\Store
     */
    protected $session;
    
    protected $old = [];

    protected function setUp()
    {
        parent::setUp();

        // Capsule::table('models')->truncate();
        Model::unguard();

        $urlGenerator = new UrlGenerator(new RouteCollection(), Request::create('/foo', 'GET'));
        $viewFactory = Mockery::mock(Factory::class);
        $htmlBuilder = new HtmlBuilder($urlGenerator, $viewFactory);
        $this->formBuilder = new FormBuilder($htmlBuilder, $urlGenerator, $viewFactory, 'abc');
        
        // Set up session
        $this->session = Mockery::mock(\Illuminate\Contracts\Session\Session::class);
        $this->session->shouldReceive('getOldInput')
            ->andReturnUsing(function($key){
                return array_get($this->old, $key);
            });
        
        $this->formBuilder->setSessionStore($this->session);
    }
    
    protected function tearDown()
    {
        parent::tearDown();
        
        Mockery::close();
    }
    
    /**
     * @param string|array $key
     * @param mixed|null $value
     * @return $this
     */
    protected function addOldData($key, $value = NULL)
    {
        if (is_null($value) && is_array($key)) {
            $this->old = array_merge($this->old, $key);
        } else {
            $this->old[$key] = $value;
        }
        
        return $this;
    }
    
    public function testFlatArray()
    {
        $model = new TestModel([
            'flat' => ['Twilight', 'Applejack', 'Rarity', ],
        ]);

        $this->formBuilder->setModel($model);
        
        $values[] = $this->formBuilder->getValueAttribute('flat[]');
        $values[] = $this->formBuilder->getValueAttribute('flat[]');
        $values[] = $this->formBuilder->getValueAttribute('flat[]');
        $values[] = $this->formBuilder->getValueAttribute('flat[]');

        $this->assertEquals(['Twilight', 'Applejack', 'Rarity', null, ], $values);
    }

    public function testNestedArray()
    {
        $model = new TestModel([
            'nested' => [
                ['name' => 'Twilight', 'color' => 'purple', ],
                ['name' => 'Applejack', 'color' => 'orange', ],
                ['name' => 'Rarity', 'color' => 'white', ],
            ],
        ]);

        $this->formBuilder->setModel($model);

        $names[] = $this->formBuilder->getValueAttribute('nested[][name]');
        $names[] = $this->formBuilder->getValueAttribute('nested[][name]');
        $names[] = $this->formBuilder->getValueAttribute('nested[][name]');
        $names[] = $this->formBuilder->getValueAttribute('nested[][name]');

        $this->assertEquals(['Twilight', 'Applejack', 'Rarity', null, ], $names);

        $colors[] = $this->formBuilder->getValueAttribute('nested[][color]');
        $colors[] = $this->formBuilder->getValueAttribute('nested[][color]');
        $colors[] = $this->formBuilder->getValueAttribute('nested[][color]');
        $colors[] = $this->formBuilder->getValueAttribute('nested[][color]');

        $this->assertEquals(['purple', 'orange', 'white', null, ], $colors);
    }

    public function testNonArrayModelData()
    {
        $modelData = [
            'integer' => 12,
            'string' => 'Hello World',
        ];

        $model = new TestModel($modelData);
        $this->formBuilder->setModel($model);

        foreach ($modelData as $key => $value) {
            $this->assertEquals($value, $this->formBuilder->getValueAttribute($key));
        }
    }
    
    public function testOldData()
    {
        $this->addOldData('flat', ['Twilight', 'Applejack', 'Rarity', ]);
    
        $values[] = $this->formBuilder->getValueAttribute('flat[]');
        $values[] = $this->formBuilder->getValueAttribute('flat[]');
        $values[] = $this->formBuilder->getValueAttribute('flat[]');
        $values[] = $this->formBuilder->getValueAttribute('flat[]');
    
        $this->assertEquals(['Twilight', 'Applejack', 'Rarity', null, ], $values);
    }
}

class FormModel extends Model
{
    use FormAccessible;
}

class TestModel extends Model
{
}