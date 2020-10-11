(
    function() {
        'use strict';
        angular
            .module('app', ['ngRoute', 'ngCookies'])
            .config(config)
            .run(run);
        config.$inject = ['$routeProvider', '$locationProvider'];

        function config($routeProvider, $locationProvider) {
            $routeProvider
                .when('/', {
                    controller: 'LoginController',
                    templateUrl: 'login.html',
                    controllerAs: 'vm'
                })
                .when('/home', {
                    controller: 'HomeController',
                    templateUrl: 'home.html',
                    controllerAs: 'vm',
                })
                .when('/register', {
                    controller: 'RegisterController',
                    templateUrl: 'register.html',
                    controllerAs: 'vm'
                })
                .otherwise({ redirectTo: '/' });
        }
        run.$inject = ['$rootScope', '$location'];

        function run($rootScope, $location) {
            if (localStorage.getItem('user_data') != null) {
                var parseddata = JSON.parse(localStorage.getItem('user_data'));
                $rootScope.user_name = parseddata.user_name;
                $rootScope.token = parseddata.token;
            } else {
                $location.path('/');
            }
        }
    })();