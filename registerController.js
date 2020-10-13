(function() {
    'use strict';
    angular
        .module('app')
        .controller('RegisterController', RegisterController);
    LoginController.$inject = ['$location', '$http', '$scope'];

    function RegisterController($location, $http, $scope) {
        var vm = this;
        vm.register = register;

        function register() {
            console.log("register method executing");
            var parameter = { name = vm.name, email = vm.email, user_name: vm.username, password: vm.password };
            vm.dataLoading = true;
            $http({
                method: "POST",
                data: parameter,
                url: "http://localhost/game/api/v1/signup.php",
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).then(function(response) {
                vm.dataLoading = false;
                console.log(response);
                var data = response.data;
                if (data.status == 200) {
                    alert(data.message);
                    localStorage.setItem("user_data", JSON.stringify({ user_name: vm.username, token: data.token }));
                    $location.path('/home');
                } else {
                    alert(data.message);
                }
            }, function(error) {
                vm.dataLoading = false;
                console(error);
            });
        }
    }
})();