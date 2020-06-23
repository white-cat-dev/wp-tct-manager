<?php get_header(); ?>

<div id="main" class="col1">
	<div class="content-posts-wrap">
		<div id="content-box">
			<div id="post-body">
				<div class="post-single">
					<h1 id="post-title">
						Корзина
					</h1>
					
					<div ng-app="tctApp" ng-controller="CartController" ng-init="init()" ng-cloak class="tct-main-cart-block">
						<div class="tct-loading-block" ng-if="isLoading">
							
						</div>
						<div ng-if="cart && cart.products.length > 0 && !isSuccessOrder">
							<table class="tct-cart-table">
								<tr>
									<th>№</th>
									<th>Название</th>
									<th>Вид</th>
									<th>Цена</th>
									<th>Количество</th>
									<th>Стоимость</th>
									<th>Наличие</th>
									<th></th>
								</tr>
								<tr ng-repeat="product in cart.products">
									<td>
										{{ $index + 1 }}
									</td>
									<td>
										{{ product.product_group.wp_name }}
									</td>
									<td>
										<select ng-model="product.variation" ng-options="otherProduct.variation as otherProduct.variation_text for otherProduct in product.otherProducts" ng-change="updateVariation(product)">
										</select>
									</td>
									<td>
										{{ product.pivot.price | number }} руб./<span ng-bind-html="product.units_text"></span>
									</td>
									<td>
										<div class="tct-cart-count-group">
											<button type="button" ng-click="updateCount(product, -1)">–</button>
											<span class="units">{{ product.pivot.count }} <span ng-bind-html="product.units_text"></span></span>
											<input type="text" ng-model="product.pivot.count" ng-blur="updateCount(product, 0)">
											<button type="button" ng-click="updateCount(product, 1)">+</button>
										</div>
									</td>
									<td>
										{{ product.pivot.cost | number }} руб.
									</td>
									<td>
										<span ng-bind-html="product.in_stock_text"></span>
									</td>
									<td style="font-size: 18px;">
										<button type="button" ng-click="removeFromCart($index)" class="tct-empty-button">✖</button>
									</td>
								</tr>
								<tr>
									<td>
										{{ cart.products.length + 1 }}
									</td>
									<td>
										Поддоны
									</td>
									<td></td>
									<td>
										150 руб./шт
									</td>
									<td>
										{{ cart.pallets }} шт
									</td>
									<td>
										{{ 150 * cart.pallets | number }} руб.
									</td>
									<td></td>
									<td></td>
								</tr>
								<tr ng-if="cart.delivery">
									<td>
										{{ cart.products.length + 2 }}
									</td>
									<td>
										Доставка
									</td>
									<td></td>
									<td>
										{{ cart.delivery_price | number }} руб.
									</td>
									<td>
										1 шт
									</td>
									<td>
										{{ cart.delivery_price | number }} руб.
									</td>
									<td></td>
									<td></td>
								</tr>
								<tr class="tct-main-row">
									<td>

									</td>
									<td>
										<strong>Итого</strong>
									</td>
									<td></td>
									<td>
									</td>
									<td>
									</td>
									<td>
										<strong>{{ cart.cost | number }} руб.</strong>
									</td>
									<td></td>
									<td></td>
								</tr>
							</table>

							<div class="tct-info-block">
								<div>
									<div class="tct-block-title">
										Доставка
									</div>
									<div class="tct-info-label">
										Выберите способ доставки:
									</div>
									<div class="tct-checkbox-group">
										<input type="radio" id="radioNone" ng-model="cart.delivery" value="" ng-change="updateCart()">
										<label for="radioNone">
											Самовывоз
										</label>
									</div>
									<div class="tct-checkbox-group">
										<input type="radio" id="radioSverdlovsk" ng-model="cart.delivery" value="sverdlovsk" ng-change="updateCart()">
										<label for="radioSverdlovsk">
											Свердловский район
										</label>
									</div>
									<div class="tct-checkbox-group">
										<input type="radio" id="radioOther" ng-model="cart.delivery" value="other" ng-change="updateCart()">
										<label for="radioOther">
											Другой район
										</label>
									</div>

									<div class="tct-small-input-group">
										<input type="text" ng-model="cart.delivery_distance" ng-blur="updateCart()">
										<span>км за городом</span>
									</div>
								</div>
								<div>
									<div class="tct-block-title">
										Детали заказа
									</div>

									<div class="tct-info-label">
										Оплата заказа:
									</div>
									<div class="tct-info-text">
										Цена указана за наличный расчет
									</div>

									<div class="tct-info-label">
										Дата готовности:
									</div>
									<div class="tct-info-text" ng-if="cart.date_to_text">
										<span ng-bind-html="cart.date_to_text"></span>
									</div>

									<button type="button" class="tct-manager-button" ng-click="getDate()" ng-disabled="isAddSaving" ng-if="!cart.date_to_text">
										Узнать дату готовности
									</button>
								</div>
							</div>

							<div class="tct-info-block">
								<div>
									<div class="tct-block-title">
										Контактная информация
									</div>
									<div class="tct-input-group">
										<label for="cartClientName">Имя</label>
										<input type="text" ng-model="cart.client.name" id="cartClientName" ng-class="{'invalid': cartErrors.client.name}">
									</div>
									<div class="tct-input-group" style="margin-bottom: 0px;">
										<label for="cartClientPhone">Телефон</label>
										<input type="text" ng-model="cart.client.phone" id="cartClientPhone" ng-class="{'invalid': cartErrors.client.phone}">
									</div>
								</div>
							</div>

							<div class="tct-buttons-block">
								<button type="button" class="tct-manager-button" ng-click="saveOrder()">
									Оформить заказ
								</button>
							</div>
						</div>

						<div ng-if="!cart || cart.products.length == 0">
							Ваша корзина пуста
						</div>

						<div ng-if="isSuccessOrder" class="tct-success-order">
							Заказ успешно принят!<br>
							Наш менеджер свяжется с вами в ближайшее время!
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>