import 'package:flutter/material.dart';
import '../models/product.dart';
import '../services/api_service.dart';

class ProductsProvider with ChangeNotifier {
  List<Product> _products = [];
  List<Product> _featuredProducts = [];
  bool _isLoading = false;

  List<Product> get products => _products;
  List<Product> get featuredProducts => _featuredProducts;
  bool get isLoading => _isLoading;

  Future<void> fetchProducts({Map<String, dynamic>? filters}) async {
    _isLoading = true;
    notifyListeners();

    try {
      final data = await ApiService.getProducts(filters: filters);
      _products = data.map((json) => Product.fromJson(json)).toList();
    } catch (e) {
      print('Error fetching products: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchFeaturedProducts() async {
    try {
      final data = await ApiService.getProducts(filters: {'featured': true});
      _featuredProducts = data.map((json) => Product.fromJson(json)).toList();
      notifyListeners();
    } catch (e) {
      print('Error fetching featured products: $e');
    }
  }
}
