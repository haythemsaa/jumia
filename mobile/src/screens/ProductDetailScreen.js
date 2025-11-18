import React, { useState, useEffect } from 'react';
import { View, Text, Image, ScrollView, TouchableOpacity, StyleSheet, ActivityIndicator } from 'react-native';
import Icon from 'react-native-vector-icons/MaterialIcons';
import Toast from 'react-native-toast-message';
import ApiService from '../services/api.service';
import { formatPrice } from '../utils/helpers';
import { Colors, Spacing, FontSizes, BorderRadius, GlobalStyles } from '../config/theme';

const ProductDetailScreen = ({ route, navigation }) => {
  const { productId } = route.params;
  const [product, setProduct] = useState(null);
  const [quantity, setQuantity] = useState(1);
  const [loading, setLoading] = useState(true);

  useEffect(() => { loadProduct(); }, []);

  const loadProduct = async () => {
    try {
      const data = await ApiService.getProduct(productId);
      setProduct(data);
    } catch (error) {
      Toast.show({ type: 'error', text1: 'Erreur de chargement' });
    } finally {
      setLoading(false);
    }
  };

  const addToCart = async () => {
    try {
      await ApiService.addToCart(productId, quantity);
      Toast.show({ type: 'success', text1: 'Ajouté au panier' });
    } catch (error) {
      Toast.show({ type: 'error', text1: 'Erreur' });
    }
  };

  if (loading) return <View style={[GlobalStyles.container, GlobalStyles.center]}><ActivityIndicator size="large" color={Colors.primary} /></View>;

  return (
    <View style={GlobalStyles.container}>
      <ScrollView>
        <Image source={{ uri: product.image }} style={styles.image} />
        <View style={styles.content}>
          <Text style={styles.name}>{product.name}</Text>
          <Text style={styles.price}>{formatPrice(product.price)}</Text>
          <Text style={styles.description}>{product.description}</Text>
          <View style={styles.quantityContainer}>
            <Text style={styles.quantityLabel}>Quantité:</Text>
            <TouchableOpacity style={styles.qtyBtn} onPress={() => setQuantity(Math.max(1, quantity - 1))}><Icon name="remove" size={20} color={Colors.white} /></TouchableOpacity>
            <Text style={styles.quantity}>{quantity}</Text>
            <TouchableOpacity style={styles.qtyBtn} onPress={() => setQuantity(quantity + 1)}><Icon name="add" size={20} color={Colors.white} /></TouchableOpacity>
          </View>
        </View>
      </ScrollView>
      <TouchableOpacity style={styles.addButton} onPress={addToCart}>
        <Text style={styles.addButtonText}>Ajouter au panier</Text>
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  image: { width: '100%', height: 300 },
  content: { padding: Spacing.lg },
  name: { fontSize: FontSizes.xxl, fontWeight: 'bold', color: Colors.textPrimary, marginBottom: Spacing.sm },
  price: { fontSize: FontSizes.xl, fontWeight: 'bold', color: Colors.primary, marginBottom: Spacing.md },
  description: { fontSize: FontSizes.md, color: Colors.textSecondary, lineHeight: 24, marginBottom: Spacing.lg },
  quantityContainer: { flexDirection: 'row', alignItems: 'center', marginBottom: Spacing.lg },
  quantityLabel: { fontSize: FontSizes.lg, marginRight: Spacing.md },
  qtyBtn: { backgroundColor: Colors.primary, width: 36, height: 36, borderRadius: 18, justifyContent: 'center', alignItems: 'center' },
  quantity: { marginHorizontal: Spacing.lg, fontSize: FontSizes.xl, fontWeight: '600' },
  addButton: { backgroundColor: Colors.primary, padding: Spacing.lg, alignItems: 'center', margin: Spacing.md, borderRadius: BorderRadius.lg },
  addButtonText: { color: Colors.white, fontSize: FontSizes.lg, fontWeight: 'bold' },
});

export default ProductDetailScreen;
