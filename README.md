## Bluecrest Test Exercise

### Thought process

- Create new laravel project following [Laravel documentation](https://laravel.com/docs/11.x)
- Deleted unnecessary junk from the repository that would not be needed
  - Any frontend files could be removed as project is an API
- Added some packages to `composer.json` that would be required
  - `laravel/sanctum` for API functionality
  - `tymon/jwt-auth` for authentication
- Create necessary routes and controllers/methods 
  - AuthenticationController to handle authentication endpoints
  - TaskController to handle task operations
- Create model and corresponding DB migration
  - Here I made the assumption that there would be a fixed set of statuses, so I created an enum for this purpose.
  - I also made the assumption that `due_date` is a required field
- I also created Request files for validation purposes
- Controller functionality
  - I made the assumption that updating the resource would be handled partially - as in if a field is not present in the
    update request then the field should remain unchanged, therefore two separate validation files would be required.
- I then created methods used for authentication, fetching the user and refreshing the access token.
  - I used JWT because I believe it fits the scale of the application better than Laravel Passport.
- I decided to use SwaggerUI to document the behaviour of the API
  - Included `nextapps/laravel-swagger-ui` to make this information public
  - Used an [online swagger editor](https://forge.etsi.org/swagger/editor/) to create the necessary openapi file 
- After this I made some adjustments to the API to better fit REST specifications
- Finally I wrote some Feature tests to test the API endpoints. 
  - Here some fixes to the codebase were required as I uncovered some issues with the update endpoint and validation

### Documentation used

- [Installation docs](https://laravel.com/docs/11.x)
- [Sanctum API docs](https://laravel.com/docs/11.x/routing#api-routes)
- [Resource docs](https://laravel.com/docs/11.x/eloquent-resources)
- [OpenAPI specs](https://swagger.io/specification/)
- [JWT-Auth docs](https://jwt-auth.readthedocs.io/en/develop/laravel-installation/)
