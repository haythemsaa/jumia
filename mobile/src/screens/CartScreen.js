/**
 * ICHRI Tunisia Mobile - Cart Screen
 */

import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  Image,
  StyleSheet,
  ActivityIndicator,
} from 'react-native';
import Icon from 'react-native-vector-icons/MaterialIcons';
import Toast from 'react-native-toast-message';
import ApiService from '../services/api.service';
import { formatPrice } from '../utils/helpers';
import { Colors, Spacing, FontSizes, BorderRadius, GlobalStyles } from '../config/theme';

const CartScreen = ({ navigation }) => {
  const [cart, setCart] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadCart();
  }, []);

  const loadCart = async () => {
    try {
      const data = await ApiService.getCart();
      setCart(data);
    } catch (error) {
      Toast.show({ type: 'error', text1: 'Erreur', text2: 'Impossible de charger le panier' });
    } finally {
      setLoading(false);
    }
  };

  const updateQuantity = async (itemId, newQuantity) => {
    if (newQuantity < 1) return;
    try {
      await ApiService.updateCart(itemId, newQuantity);
      loadCart();
      Toast.show({ type: 'success', text1: 'Panier mis à jour' });
    } catch (error) {
      Toast.show({ type: 'error', text1: 'Erreur de mise à jour' });
    }
  };

  const removeItem = async (itemId) => {
    try {
      await ApiService.removeFromCart(itemId);
      loadCart();
      Toast.show({ type: 'success', text1: 'Article supprimé' });
    } catch (error) {
      Toast.show({ type: 'error', text1: 'Erreur' });
    }
  };

  const renderItem = ({ item }) => (
    <View style={styles.cartItem}>
      <Image source={{ uri: item.product.image }} style={styles.productImage} />
      <View style={styles.productInfo}>
        <Text style={styles.productName}>{item.product.name}</Text>
        <Text style={styles.productPrice}>{formatPrice(item.price)}</Text>
        <View style={styles.quantityContainer}>
          <TouchableOpacity
            style={styles.qtyButton}
            onPress={() => updateQuantity(item.id, item.quantity - 1)}
          >
            <Icon name="remove" size={20} color={Colors.white} />
          </TouchableOpacity>
          <Text style={styles.quantity}>{item.quantity}</Text>
          <TouchableOpacity
            style={styles.qtyButton}
            onPress={() => updateQuantity(item.id, item.quantity + 1)}
          >
            <Icon name="add" size={20} color={Colors.white} />
          </TouchableOpacity>
        </View>
      </View>
      <TouchableOpacity onPress={() => removeItem(item.id)}>
        <Icon name="delete" size={24} color={Colors.error} />
      </TouchableOpacity>
    </View>
  );

  if (loading) {
    return (
      <View style={[GlobalStyles.container, GlobalStyles.center]}>
        <ActivityIndicator size="large" color={Colors.primary} />
      </View>
    );
  }

  if (!cart || !cart.items || cart.items.length === 0) {
    return (
      <View style={[GlobalStyles.container, GlobalStyles.center]}>
        <Icon name="shopping-cart" size={80} color={Colors.lightGray} />
        <Text style={styles.emptyText}>Votre panier est vide</Text>
        <TouchableOpacity
          style={styles.shopButton}
          onPress={() => navigation.navigate('Products')}
        >
          <Text style={styles.shopButtonText}>Continuer mes achats</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <View style={GlobalStyles.container}>
      <FlatList
        data={cart.items}
        renderItem={renderItem}
        keyExtractor={(item) => item.id.toString()}
        contentContainerStyle={styles.listContent}
      />

      <View style={styles.summary}>
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Sous-total:</Text>
          <Text style={styles.summaryValue}>{formatPrice(cart.subtotal)}</Text>
        </View>
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>TVA (19%):</Text>
          <Text style={styles.summaryValue}>{formatPrice(cart.tax || 0)}</Text>
        </View>
        <View style={[styles.summaryRow, styles.totalRow]}>
          <Text style={styles.totalLabel}>Total:</Text>
          <Text style={styles.totalValue}>{formatPrice(cart.total)}</Text>
        </View>
        <TouchableOpacity
          style={styles.checkoutButton}
          onPress={() => navigation.navigate('Checkout')}
        >
          <Text style={styles.checkoutButtonText}>Passer la commande</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  listContent: {
    padding: Spacing.md,
  },
  cartItem: {
    backgroundColor: Colors.white,
    borderRadius: BorderRadius.lg,
    padding: Spacing.md,
    marginBottom: Spacing.md,
    flexDirection: 'row',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  productImage: {
    width: 80,
    height: 80,
    borderRadius: BorderRadius.md,
  },
  productInfo: {
    flex: 1,
    marginLeft: Spacing.md,
  },
  productName: {
    fontSize: FontSizes.md,
    fontWeight: '600',
    color: Colors.textPrimary,
    marginBottom: Spacing.xs,
  },
  productPrice: {
    fontSize: FontSizes.lg,
    fontWeight: 'bold',
    color: Colors.primary,
    marginBottom: Spacing.sm,
  },
  quantityContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  qtyButton: {
    backgroundColor: Colors.primary,
    width: 28,
    height: 28,
    borderRadius: 14,
    justifyContent: 'center',
    alignItems: 'center',
  },
  quantity: {
    marginHorizontal: Spacing.md,
    fontSize: FontSizes.lg,
    fontWeight: '600',
  },
  emptyText: {
    marginTop: Spacing.lg,
    fontSize: FontSizes.xl,
    color: Colors.textSecondary,
  },
  shopButton: {
    marginTop: Spacing.xl,
    backgroundColor: Colors.primary,
    paddingVertical: Spacing.md,
    paddingHorizontal: Spacing.xl,
    borderRadius: BorderRadius.lg,
  },
  shopButtonText: {
    color: Colors.white,
    fontSize: FontSizes.lg,
    fontWeight: 'bold',
  },
  summary: {
    backgroundColor: Colors.white,
    padding: Spacing.lg,
    borderTopWidth: 1,
    borderTopColor: Colors.border,
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: Spacing.sm,
  },
  summaryLabel: {
    fontSize: FontSizes.md,
    color: Colors.textSecondary,
  },
  summaryValue: {
    fontSize: FontSizes.md,
    color: Colors.textPrimary,
  },
  totalRow: {
    marginTop: Spacing.md,
    paddingTop: Spacing.md,
    borderTopWidth: 1,
    borderTopColor: Colors.border,
  },
  totalLabel: {
    fontSize: FontSizes.xl,
    fontWeight: 'bold',
    color: Colors.textPrimary,
  },
  totalValue: {
    fontSize: FontSizes.xl,
    fontWeight: 'bold',
    color: Colors.primary,
  },
  checkoutButton: {
    backgroundColor: Colors.primary,
    padding: Spacing.md,
    borderRadius: BorderRadius.lg,
    alignItems: 'center',
    marginTop: Spacing.md,
  },
  checkoutButtonText: {
    color: Colors.white,
    fontSize: FontSizes.lg,
    fontWeight: 'bold',
  },
});

export default CartScreen;
