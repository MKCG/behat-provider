# Behat Provider

Define providers to be able to inject values coming from providers into Gherkin specification.

This can be used to :
* Inject values coming from a database into Gherkin features
* Inject random values to test form input for example
* Duplicate lines using a provider to inject different values

## How to test this project ?

You can clone the project and use docker-compose to execute each scenario defined in the features directory.

```bash
git clone https://github.com/MKCG/behat-provider.git && cd behat-provider && docker-compose up
```


## What is a provider ?

A provider is a callback that return an associative array each time it is called.
This is useful to inject random values corresponding to a specific domain.


## When will a provider be called ?

The provider will be called for each line feature defining a **provider** column.


## How to define a provider ?

A provider is defined by a **name** and a *callback*.

The **name** is used to register the provider into the **injector** and must be the same defined into the *provider* column.
The **callback** is a function taking no parameter and returning an associative array.

To use a provider in a feature, the tag **provider** must be defined for each **scenario** and a column named **provider** must contains the provider to use.


Example of a **provider** being registered to an **injector** :

```php
$injector = new \MKCG\Behat\Provider\Injector()
$injector->addProvider('user', function() {
    $faker = Faker\Factory::create();

    return [
        'firstname' => $faker->firstName,
        'lastname' => $faker->lastname,
        'email' => $faker->email
    ];
});
```

Example of an usage of this provider in a feature :

```gherkin
Feature: Values can be injected into Gherkin Scenario definitions

    @provider
    Scenario Outline: Values can be injected for lines specifying a provider
        Given the form "<form_name>"
        When the field "firstname" is filled with "<firstname>"
            And the field "lastname" is filled with "<lastname>"
            And the field "email" is filled with "<email>"
        Then the submitted form is
        """
            {
                "firstname": "<firstname>",
                "lastname": "<lastname>",
                "email": "<email>"
            }
        """

        Examples:
        | provider  | firstname     | lastname      | email     |
        | user      | <firstname>   | <lastname>    | <email>   |
```

## How to inject values into Gherkin features ?

Values can be injected for **Scenario Outline** into the **Example** table using the Behat Hook **BeforeFeature**.

Example :

```php

    /**
     * @BeforeFeature
     */
    public static function prepare($scope)
    {   
        $injector = new \MKCG\Behat\Provider\Injector()
        $injector->injectValues($scope->getFeature());
    }
```

## How to generate multiple random lines ?

When testing a feature, it might be useful to generate a lot of random test for a specific feature.
It is possible to duplicate each scenario examples using a provider by using the tag **provider-copy**.

Each line using a provider of each scenario outline using this tag will be duplicated the number of times specified in the constructor of the Injector class.

As lines will be duplicated before the injection of provided values, then this can be useful to generate a lot of random values when testing a form.


Example of an Injector specifying 10 copies of each line :

```php
 $injector = new \MKCG\Behat\Provider\Injector(10);
```


Example of its usage with a Scenario Outline :

```gherkin
Feature: Values can be injected into Gherkin Scenario definitions
    @provider @provider-copy
    Scenario Outline: The "provider-copy" tag can be used to copy "n" times the definition of each line using a provider
        Given the form "<form_name>"
        When the field "first_fied" is filled with "<first_field>"
            And the field "second_field" is filled with "<second_field>"
        Then the submitted form is
        """
            {
                "first_field": "<first_field>",
                "second_field": "<second_field>"
            }
        """

        Examples:
        | provider  | first_field   | second_field  |
        |           | Kévin         | Masseix       |
        | user      | <firstname>   | <lastname>    |
        | address   | <postcode>    | <city>        |
```
