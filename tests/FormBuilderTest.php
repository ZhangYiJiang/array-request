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

    protected function setUp()
    {
        parent::setUp();

        // Capsule::table('models')->truncate();
        Model::unguard();

        $urlGenerator = new UrlGenerator(new RouteCollection(), Request::create('/foo', 'GET'));
        $viewFactory = Mockery::mock(Factory::class);
        $htmlBuilder = new HtmlBuilder($urlGenerator, $viewFactory);
        $this->formBuilder = new FormBuilder($htmlBuilder, $urlGenerator, $viewFactory, 'abc');
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
}

class FormModel extends Model
{
    use FormAccessible;

    protected $table = 'models';


}

class TestModel extends Model
{
    protected $table = 'models';
}