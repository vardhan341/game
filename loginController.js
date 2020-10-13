(function() {
    'use strict';
    angular
        .module('app')
        .controller('LoginController', LoginController);
    LoginController.$inject = ['$location', '$http', '$rootScope'];

    function LoginController($location, $http, $rootScope) {
        var vm = this;
        vm.login = login;

        function login() {
            var parameter = JSON.stringify({ user_name: vm.username, password: vm.password });
            vm.dataLoading = true;
            $http({
                method: "POST",
                data: parameter,
                url: "http://localhost/game/api/v1/login.php",
                headers: { 'Content-Type': 'application/json' }
            }).then(function(response) {
                    vm.dataLoading = false;
                    var data = response.data;
                    if (data.status == 200) {
                        if (data.hasOwnProperty('data')) {
                            localStorage.setItem("user_data", JSON.stringify({ user_name: vm.username, token: data.token }));
                            $rootScope.user_name = vm.username;
                            $rootScope.token = data.token;
                            $location.path('/home');
                        } else {
                            alert(data.message);
                        }
                    }
                },
                function(error) {
                    vm.dataLoading = false;
                    console(error);
                });
        }
    }
})();