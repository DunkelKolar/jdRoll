angular.module("jdRoll.controller.main", ["jdRoll.service.session"]).
controller('MainController',function($scope,SessionService) {

    $scope.authentInfo=function(){
        return SessionService.getAuthentInfo();
	};

});