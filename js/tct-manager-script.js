var tctApp = angular.module('tctApp', ['ngResource', 'ngCookies']);

var prefix = 'https://manager.582907.ru/wp-api';


tctApp.config(function($provide) {
    $provide.value('$locale', {
	    "NUMBER_FORMATS": {
			"CURRENCY_SYM": "\u20bd",
			"DECIMAL_SEP": ",",
			"GROUP_SEP": "\u00a0",
			"PATTERNS": [
				{
					"gSize": 3,
					"lgSize": 3,
					"maxFrac": 3,
					"minFrac": 0,
					"minInt": 1,
					"negPre": "-",
					"negSuf": "",
					"posPre": "",
					"posSuf": ""
				}
			]
		}
	});
});


tctApp.factory('ProductRepository', ['$resource', function($resource) { 
	return $resource(prefix + '/product', null, {
		stock: { method: 'GET', url: prefix + '/product/stock' }
    });  
}]);


tctApp.factory('OrderRepository', ['$resource', function($resource) { 
	return $resource(prefix + '/order', null, {
		add: { method: 'POST', url: prefix + '/order/add' },
		getDate: { method: 'POST', url: prefix + '/order/date' },
		update: { method: 'POST', url: prefix + '/order/update' }
    });  
}]);


tctApp.controller('toCartController', [
	'$scope', '$cookies', '$sce', 'ProductRepository', 'OrderRepository',
	function($scope, $cookies, $sce, ProductRepository, OrderRepository)
	{
		$scope.title = '';
		$scope.units = '';
		$scope.productGroup = [];

		$scope.isButtonShown = false;
		$scope.isLinkShown = false;
		$scope.isLoading = false;

		$scope.toCartData = {
			'id': 0,
			'count': 10
		};

		$scope.init = function(title)
		{
			$scope.title = title;

			ProductRepository.stock({'title': $scope.title}, function(response) 
			{
				if (response) 
				{
					$scope.isToCartShown = true;
					$scope.productGroup = response;
					$scope.units = $sce.trustAsHtml($scope.productGroup.units);
					$scope.toCartData.id = $scope.productGroup.id;

					for (product of $scope.productGroup.products)
					{
						product.in_stock = $sce.trustAsHtml(product.in_stock);
						product.in_stock_pair = $sce.trustAsHtml(product.in_stock_pair);
					}
				}
			});
		}


		$scope.addToCart = function()
		{
			var tctManagerCart = $cookies.get('tct_manager_cart');
			if (tctManagerCart)
			{
				$scope.toCartData['tct_manager_cart'] = tctManagerCart;
			}

			$scope.isLoading = true;

			OrderRepository.add($scope.toCartData, function(response) 
			{
				if (response.tct_manager_cart)
				{
					$cookies.put('tct_manager_cart', response.tct_manager_cart, {'path': '/'});
				}
				$scope.isLinkShown = true;
			});
		}


		$scope.updateCount = function()
		{
			if ($scope.toCartData.count.length > 6)
			{
				$scope.toCartData.count = $scope.toCartData.count.substring(0, 6);
			}

			$scope.toCartData.count = $scope.toCartData.count.replace(',', '.');
			$scope.toCartData.count = $scope.toCartData.count.replace(/[^.\d]/g, '');

			if ($scope.toCartData.count.split('.').length - 1 > 1)
			{
				var index = $scope.toCartData.count.indexOf('.');
				var count = $scope.toCartData.count.substring(0, index);
				$scope.toCartData.count = $scope.toCartData.count.substring(index).replace('.', '');
			}
		}
	}
]);


tctApp.controller('CartController', [
	'$scope', '$cookies', '$sce', 'ProductRepository', 'OrderRepository',
	function($scope, $cookies, $sce, ProductRepository, OrderRepository)
	{
		$scope.cart = {};

		$scope.cartErrors = {
			'client': {}
		};

		$scope.isButtonShown = false;
		$scope.isLinkShown = false;
		$scope.isLoading = false;

		$scope.toCartData = {
			'id': 0,
			'count': 10
		};

		$scope.init = function(title)
		{
			$scope.isLoading = true;

			OrderRepository.get({'tct_manager_cart': $cookies.get('tct_manager_cart')}, function(response) 
			{
				$scope.isLoading = false;

				if (response) 
				{
					$scope.cart = response;
					$scope.updateCart();

					for (product of $scope.cart.products)
					{
						product.units_text = $sce.trustAsHtml(product.units_text);
						product.in_stock_text = $sce.trustAsHtml(product.in_stock_text);
					}
				}
			});
		}

		$scope.updateVariation = function(product)
		{
			for (otherProduct of product.otherProducts)
			{
				if (product.variation == otherProduct.variation)
				{
					product.id = otherProduct.id;
					product.pivot.price = otherProduct.price;
					break;
				}
			}
			$scope.updateCart();
		}


		$scope.updateCount = function(product, count)
		{
			product.pivot.count = '' + product.pivot.count;

			if (product.pivot.count.length > 6)
			{
				product.pivot.count = product.pivot.count.substring(0, 6);
			}
			product.pivot.count = product.pivot.count.replace(/[^,.\d]/g, '');
			product.pivot.count = product.pivot.count.replace('.', ',');

			if (product.pivot.count.split(',').length - 1 > 1)
			{
				var index = product.pivot.count.indexOf(',');
				var count = product.pivot.count.substring(0, index);
				product.pivot.count = product.pivot.count.substring(index).replace(',', '');
			}

			product.pivot.count = +product.pivot.count + count;

			$scope.updateCart();
		}


		$scope.removeFromCart = function(index) 
		{
			$scope.cart.products.splice(index, 1);

			$scope.updateCart();
		}


		$scope.updateCart = function()
		{
			$scope.cart.cost = 0;
			$scope.cart.weight = 0;
			$scope.cart.pallets = 0;

			for (product of $scope.cart.products) 
			{
				product.pivot.cost = product.pivot.price * product.pivot.count;

				$scope.cart.cost += product.pivot.cost;
				$scope.cart.weight += product.product_group.weight_unit * product.product_group.unit_in_units * product.pivot.count;
				$scope.cart.pallets += Math.ceil(product.pivot.count / product.product_group.units_in_pallete); 
			}

			if ($scope.cart.delivery)
			{
				switch ($scope.cart.delivery) {
					case ('sverdlovsk'):
						$scope.cart.delivery_price = 2500;
					    break;

					case ('other'):
						$scope.cart.delivery_price = 3000;
						break;
				}

				if ($scope.cart.delivery_distance)
				{
					$scope.cart.delivery_price += 50 * $scope.cart.delivery_distance;
				}

				$scope.cart.cost += $scope.cart.delivery_price;
			}

			$scope.cart.cost += 150 * $scope.cart.pallets;
			$scope.cart.cost = Math.ceil($scope.cart.cost);

			$scope.isLoading = true;

			OrderRepository.update($scope.cart, function(response)
			{
				$scope.isLoading = false;

				$scope.cart = response;

				for (product of $scope.cart.products)
				{
					product.units_text = $sce.trustAsHtml(product.units_text);
					product.in_stock_text = $sce.trustAsHtml(product.in_stock_text);
				}

				$scope.cart.date_to_text = '';
			});
		}


		$scope.getDate = function() 
		{
			$scope.isAddSaving = true;

			OrderRepository.getDate($scope.cart, function(response) 
			{
				$scope.isAddSaving = false;

				$scope.cart.date_to_text = $sce.trustAsHtml(response.date_text);
			});
		}


		$scope.saveOrder = function()
		{
			$scope.cartErrors.client.name = !$scope.cart.client.name;
			$scope.cartErrors.client.phone = !$scope.cart.client.phone;

			if ($scope.cartErrors.client.name || $scope.cartErrors.client.phone)
			{
				return;
			}

			$scope.isSaving = true;
			OrderRepository.save($scope.cart, function(response)
			{
				$scope.isSuccessOrder = true;
			});
		}
	}
]);