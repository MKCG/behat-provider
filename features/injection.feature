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

    @provider
    Scenario Outline: Multiple providers can be defined
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
        | user      | <firstname>   | <lastname>    |
        | address   | <postcode>    | <city>        |

    @provider
    Scenario Outline: the "provider" column can be anywhere
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
        | first_field   | provider  | second_field  |
        | <firstname>   | user      | <lastname>    |
        | <postcode>    | address   | <city>        |

    @provider
    Scenario Outline: The "provider" column can be left empty
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
        | address   | <postcode>    | <city>        |

    @provider
    Scenario Outline: The "provider" column can be used to partially fill a line
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
        | user      | Kévin         | <lastname>    |
        | address   | <postcode>    | <city>        |

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
