# SimpleLDAP

SimpleLDAP is a small library that implements an abstraction layer for LDAP server communication using PHP. It makes your life easier when you need to authenticate users through an LDAP server and/or when you need to perform CRUD actions on it. It's meant to be simple and easy to use. If you need more robust solutions, feel free to expand from SimpleLDAP and create... well, ComplexLDAP. 

## Authors and contributors
* [Klaus Silveira](http://www.klaussilveira.com) (Creator, developer, support)

## License
[New BSD license](http://www.opensource.org/licenses/bsd-license.php)

## Roadmap
* Keep the code clean and easy to understand
* Keep the features easy to use
* Improve the documentation, as well comments

## Status
SimpleLDAP is currently under development by the community and is just starting. Feel free to suggest improvements and provide heavy feedback. We really want to hear it! (Seriously, we do!)

## Todo
* better comments for the code, not just methods and properties
* create a better documentation
* error handling can be improved, should focus on that
* develop new features based on user-feedback
* new features will be made, but always remembering of the library name... SimpleLDAP
* test, test, test

## Using SimpleLDAP
The idea behind SimpleLDAP is to keep things very easy to use, without headaches. In order to start using SimpleLDAP, you'll have to provide a few details, otherwise it won't be able to do it's magic. 

```php
$ldap = new LDAP('192.168.0.1', 389, 3); // Host, port and server protocol (this one is optional)
$ldap->dn = 'ou=users,dc=demo,dc=com'; // The default DN (Distinguished Name)
```

That's it. Now you're able to connect and authenticate to an LDAP server. 

```php
$ldap = new LDAP('192.168.0.1', 389, 3);
$ldap->dn = 'ou=users,dc=demo,dc=com';
print_r($ldap->auth('demo', 123456));
```

The auth method will return the user information as an array if the authentication is successful, and false if it wasn't.

###CRUD Actions
If you want to perform administrative actions on the server, such as CRUD, you'll have to bind as an user with administrative rights. That's what the ADN and APass properties are for. They are required for the CRUD actions to be performed correctly. 

```php
$ldap = new LDAP('192.168.0.1', 389, 3);
$ldap->dn = 'ou=users,dc=demo,dc=com';
$ldap->adn = 'cn=admin,dc=demo,dc=com';
$ldap->apass = '987654';
```

Now you can add, remove, modify and list users on the server. 

####Listing users
You can list users based on a filter and SimpleLDAP will return an array with information about each users that matched that filter. You can read more about those filters here: http://www.mozilla.org/directory/csdk-docs/filter.htm

```php
$ldap = new LDAP('192.168.0.1', 389, 3);
$ldap->dn = 'ou=users,dc=demo,dc=com';
$ldap->adn = 'cn=admin,dc=demo,dc=com';
$ldap->apass = '987654';
print_r($ldap->getUsers('(!(description=warcraft))'));
```

####Creating users
In order to create users, you just need to pass the username you want to create and it's directory information. The directory information should be inside an array. 

```php
$ldap = new LDAP('192.168.0.1', 389, 3);
$ldap->dn = 'ou=users,dc=demo,dc=com';
$ldap->adn = 'cn=admin,dc=demo,dc=com';
$ldap->apass = '987654';

$data['cn'][] = 'James';
$data['sn'][] = 'Bond';
$data['uid'][] = 'james';
$data['userpassword'][] = '123456';

$ldap->addUser('james', $data);
```

####Removing users
In order to remove users, you just need to pass the username you want to remove.

```php
$ldap = new LDAP('192.168.0.1', 389, 3);
$ldap->dn = 'ou=users,dc=demo,dc=com';
$ldap->adn = 'cn=admin,dc=demo,dc=com';
$ldap->apass = '987654';

$ldap->removeUser('james');
```

####Modifying users
In order to modify users, you just need to pass the username you want to modify and the new information.

```php
$ldap = new LDAP('192.168.0.1', 389, 3);
$ldap->dn = 'ou=users,dc=demo,dc=com';
$ldap->adn = 'cn=admin,dc=demo,dc=com';
$ldap->apass = '987654';

$data['sn'][] = 'Bonded';

$ldap->modifyUser('james', $data);
```
