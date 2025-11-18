/**
 * ICHRI Tunisia Mobile - Product Card Component
 */

import React from 'react';
import {
  View,
  Text,
  Image,
  TouchableOpacity,
  StyleSheet,
} from 'react-native';
import Icon from 'react-native-vector-icons/MaterialIcons';
import { Colors, Spacing, FontSizes, BorderRadius, Shadows } from '../config/theme';
import { formatPrice, calculateDiscount, truncateText } from '../utils/helpers';

const ProductCard = ({ product, onPress, onAddToCart, onAddToWishlist }) => {
  const discount = product.compare_price
    ? calculateDiscount(product.compare_price, product.price)
    : 0;

  return (
    <TouchableOpacity style={styles.container} onPress={() => onPress(product)}>
      {/* Discount Badge */}
      {discount > 0 && (
        <View style={styles.discountBadge}>
          <Text style={styles.discountText}>-{discount}%</Text>
        </View>
      )}

      {/* Wishlist Button */}
      <TouchableOpacity
        style={styles.wishlistButton}
        onPress={() => onAddToWishlist(product.id)}
      >
        <Icon name="favorite-border" size={20} color={Colors.error} />
      </TouchableOpacity>

      {/* Product Image */}
      <Image
        source={{ uri: product.image }}
        style={styles.image}
        resizeMode="cover"
      />

      {/* Product Info */}
      <View style={styles.info}>
        <Text style={styles.name} numberOfLines={2}>
          {product.name}
        </Text>

        {/* Rating */}
        {product.average_rating && (
          <View style={styles.rating}>
            <Icon name="star" size={14} color={Colors.warning} />
            <Text style={styles.ratingText}>{product.average_rating.toFixed(1)}</Text>
            <Text style={styles.reviewCount}>({product.reviews_count})</Text>
          </View>
        )}

        {/* Price */}
        <View style={styles.priceContainer}>
          <Text style={styles.price}>{formatPrice(product.price)}</Text>
          {discount > 0 && (
            <Text style={styles.oldPrice}>{formatPrice(product.compare_price)}</Text>
          )}
        </View>

        {/* Add to Cart Button */}
        <TouchableOpacity
          style={styles.addButton}
          onPress={() => onAddToCart(product.id)}
        >
          <Icon name="add-shopping-cart" size={16} color={Colors.white} />
          <Text style={styles.addButtonText}>Ajouter</Text>
        </TouchableOpacity>
      </View>
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  container: {
    width: '48%',
    backgroundColor: Colors.white,
    borderRadius: BorderRadius.lg,
    marginBottom: Spacing.md,
    ...Shadows.md,
  },
  discountBadge: {
    position: 'absolute',
    top: Spacing.sm,
    left: Spacing.sm,
    backgroundColor: Colors.error,
    borderRadius: BorderRadius.sm,
    paddingHorizontal: Spacing.sm,
    paddingVertical: 4,
    zIndex: 1,
  },
  discountText: {
    color: Colors.white,
    fontSize: FontSizes.xs,
    fontWeight: 'bold',
  },
  wishlistButton: {
    position: 'absolute',
    top: Spacing.sm,
    right: Spacing.sm,
    backgroundColor: Colors.white,
    borderRadius: BorderRadius.full,
    width: 32,
    height: 32,
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 1,
    ...Shadows.sm,
  },
  image: {
    width: '100%',
    height: 150,
    borderTopLeftRadius: BorderRadius.lg,
    borderTopRightRadius: BorderRadius.lg,
  },
  info: {
    padding: Spacing.sm,
  },
  name: {
    fontSize: FontSizes.md,
    fontWeight: '600',
    color: Colors.textPrimary,
    marginBottom: Spacing.xs,
  },
  rating: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: Spacing.xs,
  },
  ratingText: {
    fontSize: FontSizes.sm,
    fontWeight: '600',
    color: Colors.textPrimary,
    marginLeft: 2,
  },
  reviewCount: {
    fontSize: FontSizes.xs,
    color: Colors.textSecondary,
    marginLeft: 4,
  },
  priceContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: Spacing.sm,
  },
  price: {
    fontSize: FontSizes.lg,
    fontWeight: 'bold',
    color: Colors.primary,
  },
  oldPrice: {
    fontSize: FontSizes.sm,
    color: Colors.textSecondary,
    textDecorationLine: 'line-through',
    marginLeft: Spacing.xs,
  },
  addButton: {
    backgroundColor: Colors.primary,
    borderRadius: BorderRadius.sm,
    paddingVertical: Spacing.xs,
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
  },
  addButtonText: {
    color: Colors.white,
    fontSize: FontSizes.sm,
    fontWeight: '600',
    marginLeft: 4,
  },
});

export default ProductCard;
