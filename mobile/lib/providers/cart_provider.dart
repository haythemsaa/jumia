import 'package:flutter/material.dart';
import '../services/api_service.dart';

class CartProvider with ChangeNotifier {
  Map<String, dynamic>? _cart;
  bool _isLoading = false;

  Map<String, dynamic>? get cart => _cart;
  bool get isLoading => _isLoading;

  int get itemCount {
    if (_cart == null || _cart!['items'] == null) return 0;
    return (_cart!['items'] as List).fold(
      0,
      (sum, item) => sum + (item['quantity'] as int),
    );
  }

  double get total {
    if (_cart == null || _cart!['items'] == null) return 0;
    return (_cart!['items'] as List).fold(
      0.0,
      (sum, item) =>
          sum + (item['price'] as num * item['quantity'] as num).toDouble(),
    );
  }

  Future<void> fetchCart() async {
    _isLoading = true;
    notifyListeners();

    try {
      _cart = await ApiService.getCart();
    } catch (e) {
      print('Error fetching cart: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> addToCart(int productId, int quantity, {int? variantId}) async {
    try {
      await ApiService.addToCart(productId, quantity, variantId: variantId);
      await fetchCart();
    } catch (e) {
      print('Error adding to cart: $e');
      rethrow;
    }
  }
}
