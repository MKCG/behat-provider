<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

use MKCG\Behat\Provider\Injector;

class FeatureContext implements Context
{
    private $form;

    /**
     * @BeforeFeature
     */
    public static function prepare($scope)
    {   
        $injector = (new Injector(10))
            ->addProvider('user', function() {
                $faker = Faker\Factory::create();

                return [
                    'firstname' => $faker->firstName,
                    'lastname' => $faker->lastname,
                    'email' => $faker->email
                ];
            })
            ->addProvider('address', function() {
                $faker = Faker\Factory::create();

                return [
                    'postcode' => $faker->postCode,
                    'city' => $faker->city
                ];
            })
        ;

        $injector->injectValues($scope->getFeature());
    }

    /**
     * @Given the form :name
     */
    public function theForm($name)
    {
        $this->form = [
            'name' => $name,
            'fields' => []
        ];
    }

    /**
     * @When the field :field is filled with :value
     */
    public function theFieldIsFilledWith($field, $value)
    {
        $this->form['fields'][$field] = $value;
    }

    /**
     * @Then the submitted form is
     */
    public function theSubmittedFormIs(PyStringNode $string)
    {
        $values = json_decode(trim($string->getRaw()), JSON_OBJECT_AS_ARRAY);

        $missing = array_diff($this->form['fields'], $values);
        $unexpected = array_diff($values, $this->form['fields']);

        if ($missing !== []) {
            throw new \Exception("Missing : " . json_encode($missing));
        }

        if ($unexpected !== []) {
            throw new \Exception("Unexpected : " . json_encode($unexpected));
        }
    }
}
