/**
 * ICHRI Tunisia Mobile - Products Screen
 */

import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  StyleSheet,
  TextInput,
  ActivityIndicator,
} from 'react-native';
import Icon from 'react-native-vector-icons/MaterialIcons';
import Toast from 'react-native-toast-message';
import ProductCard from '../components/ProductCard';
import ApiService from '../services/api.service';
import { Colors, Spacing, FontSizes, BorderRadius, GlobalStyles } from '../config/theme';

const ProductsScreen = ({ navigation, route }) => {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [searchQuery, setSearchQuery] = useState('');
  const [sortBy, setSortBy] = useState('newest');

  const { categoryId } = route.params || {};

  useEffect(() => {
    loadProducts(1);
  }, [categoryId, sortBy]);

  useEffect(() => {
    const delaySearch = setTimeout(() => {
      if (searchQuery || searchQuery === '') {
        loadProducts(1);
      }
    }, 500);

    return () => clearTimeout(delaySearch);
  }, [searchQuery]);

  const loadProducts = async (pageNum) => {
    try {
      if (pageNum === 1) {
        setLoading(true);
      } else {
        setLoadingMore(true);
      }

      const params = {
        page: pageNum,
        per_page: 10,
        sortBy,
      };

      if (categoryId) params.category = categoryId;
      if (searchQuery) params.search = searchQuery;

      const response = await ApiService.getProducts(params);

      if (pageNum === 1) {
        setProducts(response.data);
      } else {
        setProducts([...products, ...response.data]);
      }

      setPage(pageNum);
      setTotalPages(response.last_page);
    } catch (error) {
      Toast.show({
        type: 'error',
        text1: 'Erreur',
        text2: 'Impossible de charger les produits',
      });
    } finally {
      setLoading(false);
      setLoadingMore(false);
    }
  };

  const loadMore = () => {
    if (!loadingMore && page < totalPages) {
      loadProducts(page + 1);
    }
  };

  const handleAddToCart = async (productId) => {
    try {
      await ApiService.addToCart(productId, 1);
      Toast.show({
        type: 'success',
        text1: 'Succès',
        text2: 'Produit ajouté au panier',
      });
    } catch (error) {
      Toast.show({
        type: 'error',
        text1: 'Erreur',
        text2: 'Veuillez vous connecter',
      });
    }
  };

  const handleAddToWishlist = async (productId) => {
    try {
      await ApiService.addToWishlist(productId);
      Toast.show({
        type: 'success',
        text1: 'Succès',
        text2: 'Ajouté aux favoris',
      });
    } catch (error) {
      Toast.show({
        type: 'error',
        text1: 'Erreur',
        text2: 'Veuillez vous connecter',
      });
    }
  };

  const renderHeader = () => (
    <View style={styles.header}>
      {/* Search Bar */}
      <View style={styles.searchContainer}>
        <Icon name="search" size={24} color={Colors.gray} />
        <TextInput
          style={styles.searchInput}
          placeholder="Rechercher..."
          value={searchQuery}
          onChangeText={setSearchQuery}
        />
        {searchQuery.length > 0 && (
          <TouchableOpacity onPress={() => setSearchQuery('')}>
            <Icon name="close" size={20} color={Colors.gray} />
          </TouchableOpacity>
        )}
      </View>

      {/* Sort & Filter */}
      <View style={styles.toolbar}>
        <TouchableOpacity
          style={styles.sortButton}
          onPress={() => {
            // Toggle sort order
            setSortBy(sortBy === 'newest' ? 'price_asc' : 'newest');
          }}
        >
          <Icon name="sort" size={20} color={Colors.primary} />
          <Text style={styles.sortText}>
            {sortBy === 'newest' ? 'Plus récent' : 'Prix croissant'}
          </Text>
        </TouchableOpacity>

        <Text style={styles.resultsText}>
          {products.length} produit{products.length > 1 ? 's' : ''}
        </Text>
      </View>
    </View>
  );

  const renderProduct = ({ item, index }) => (
    <View style={index % 2 === 0 ? styles.productLeft : styles.productRight}>
      <ProductCard
        product={item}
        onPress={(p) => navigation.navigate('ProductDetail', { productId: p.id })}
        onAddToCart={handleAddToCart}
        onAddToWishlist={handleAddToWishlist}
      />
    </View>
  );

  const renderFooter = () => {
    if (!loadingMore) return null;
    return (
      <View style={styles.footer}>
        <ActivityIndicator size="small" color={Colors.primary} />
      </View>
    );
  };

  const renderEmpty = () => (
    <View style={styles.emptyContainer}>
      <Icon name="search-off" size={64} color={Colors.lightGray} />
      <Text style={styles.emptyText}>Aucun produit trouvé</Text>
    </View>
  );

  if (loading) {
    return (
      <View style={[GlobalStyles.container, GlobalStyles.center]}>
        <ActivityIndicator size="large" color={Colors.primary} />
      </View>
    );
  }

  return (
    <View style={GlobalStyles.container}>
      <FlatList
        data={products}
        renderItem={renderProduct}
        keyExtractor={(item) => item.id.toString()}
        ListHeaderComponent={renderHeader}
        ListFooterComponent={renderFooter}
        ListEmptyComponent={renderEmpty}
        numColumns={2}
        columnWrapperStyle={styles.row}
        contentContainerStyle={styles.listContent}
        onEndReached={loadMore}
        onEndReachedThreshold={0.5}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  header: {
    backgroundColor: Colors.white,
    padding: Spacing.md,
    paddingTop: 50,
  },
  searchContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.background,
    borderRadius: BorderRadius.lg,
    padding: Spacing.sm,
    marginBottom: Spacing.md,
  },
  searchInput: {
    flex: 1,
    marginLeft: Spacing.sm,
    fontSize: FontSizes.md,
    color: Colors.textPrimary,
  },
  toolbar: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  sortButton: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  sortText: {
    marginLeft: Spacing.xs,
    color: Colors.primary,
    fontSize: FontSizes.md,
    fontWeight: '600',
  },
  resultsText: {
    color: Colors.textSecondary,
    fontSize: FontSizes.sm,
  },
  listContent: {
    paddingHorizontal: Spacing.md,
    paddingBottom: Spacing.xl,
  },
  row: {
    justifyContent: 'space-between',
  },
  productLeft: {
    marginRight: Spacing.xs,
  },
  productRight: {
    marginLeft: Spacing.xs,
  },
  footer: {
    paddingVertical: Spacing.lg,
    alignItems: 'center',
  },
  emptyContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: Spacing.xxl * 2,
  },
  emptyText: {
    marginTop: Spacing.md,
    fontSize: FontSizes.lg,
    color: Colors.textSecondary,
  },
});

export default ProductsScreen;
