import React, { useState, useEffect } from 'react';
import { View, FlatList, StyleSheet } from 'react-native';
import ProductCard from '../components/ProductCard';
import ApiService from '../services/api.service';
import { Colors, Spacing } from '../config/theme';

const WishlistScreen = ({ navigation }) => {
  const [wishlist, setWishlist] = useState([]);

  useEffect(() => { loadWishlist(); }, []);

  const loadWishlist = async () => {
    try {
      const data = await ApiService.getWishlist();
      setWishlist(data || []);
    } catch (error) {}
  };

  const renderProduct = ({ item }) => (
    <View style={{ width: '48%' }}>
      <ProductCard product={item.product} onPress={(p) => navigation.navigate('ProductDetail', { productId: p.id })} onAddToCart={() => {}} onAddToWishlist={() => {}} />
    </View>
  );

  return (
    <View style={styles.container}>
      <FlatList data={wishlist} renderItem={renderProduct} keyExtractor={item => item.id.toString()} numColumns={2} columnWrapperStyle={styles.row} contentContainerStyle={styles.list} />
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  list: { padding: Spacing.md },
  row: { justifyContent: 'space-between' },
});

export default WishlistScreen;
