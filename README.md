# Add interaction to composer create-project

[![Build Status](https://travis-ci.org/pierrerolland/composer-interaction.svg?branch=master)](https://travis-ci.org/pierrerolland/composer-interaction)

This composer plugin allows your `composer create-project` command to ask some information that would be necessary to either install optional new packages, or replace some placeholders after the installation.

With a piece of configuration set in your application skeleton's `composer.json`, composer will ask any kind of question, and perform actions accordingly.

Plus, the plugin auto-destroys after the project has been created, leaving your final `composer.json` clean.

## Usage

Add the following line in your skeleton's `composer.json`:

```json
{
  "require": {
    "rollandrock/composer": "^1.0"
  },
  "extra": {
    "rollandrock-interaction": {
      "questions": [
        {
          "type": "add-package",
          "question": "Would you like to do stuff?"
        },
        {
          "type": "replace",
          "question": "What is the name of your dog?"
        }
      ]
    }
  }
}
```

### Questions configuration

There are currently two actions that will follow your questions: "add-package" and "replace".

#### add-package

This action will install all the packages needed if the user that installs the project answers "yes" to the question. It can also add environment variables that would be needed by your project (optional). Here is an example of configuration for that kind of questions:

```json
{
  "type": "add-package",
  "question": "Would you like to add a bundle to help you configure RabbitMQ vhosts?",
  "packages": {
    "olaurendeau/rabbit-mq-admin-toolkit-bundle": "^2.0"
  },
  "env": {
    "RABBITMQ_USER": "rollandrock"
  }
}
```

If the user answers "yes", composer will install `olaurendeau/rabbit-mq-admin-toolkit-bundle` and add `RABBITMQ_USER` to the `.env` file.

#### replace

This action will search for a given placeholder and replace it with the user's answer. Questions can have two types, "free" or "choice". If "choice" is selected, you'll need to add an extra key "choices". Example:

```json
{
  "action": "replace",
  "question": "What is the application's name?",
  "type": "free",
  "placeholders": [
    {
      "file": "config/parameters.yaml",
      "placeholder": "{APP_NAME}"
    }
  ]
},
{
  "action": "replace",
  "question": "What is the application's type?",
  "type": "choice",
  "choices": ["api", "worker", "service"],
  "placeholders": [
    {
      "file": "config/parameters.yaml",
      "placeholder": "{APP_TYPE}"
    }
  ]
}
```

### Auto-removal

This plugin self destroys after the project has been created.
