class Product {
  final int id;
  final String name;
  final String? description;
  final double price;
  final double? salePrice;
  final String? imageUrl;
  final double rating;
  final int totalReviews;
  final String stockStatus;

  Product({
    required this.id,
    required this.name,
    this.description,
    required this.price,
    this.salePrice,
    this.imageUrl,
    required this.rating,
    required this.totalReviews,
    required this.stockStatus,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'],
      name: json['name'],
      description: json['description'],
      price: double.parse(json['price'].toString()),
      salePrice: json['sale_price'] != null
          ? double.parse(json['sale_price'].toString())
          : null,
      imageUrl: json['images'] != null && json['images'].isNotEmpty
          ? json['images'][0]['image_url']
          : null,
      rating: double.parse(json['rating'].toString()),
      totalReviews: json['total_reviews'],
      stockStatus: json['stock_status'],
    );
  }

  double get effectivePrice => salePrice ?? price;

  bool get hasDiscount => salePrice != null && salePrice! < price;

  int get discountPercentage {
    if (!hasDiscount) return 0;
    return (((price - salePrice!) / price) * 100).round();
  }
}
