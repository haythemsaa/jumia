/**
 * ICHRI Tunisia Mobile - Home Screen
 */

import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  ScrollView,
  FlatList,
  TouchableOpacity,
  StyleSheet,
  RefreshControl,
} from 'react-native';
import Icon from 'react-native-vector-icons/MaterialIcons';
import LinearGradient from 'react-native-linear-gradient';
import Swiper from 'react-native-swiper';
import Toast from 'react-native-toast-message';
import ProductCard from '../components/ProductCard';
import ApiService from '../services/api.service';
import { Colors, Spacing, FontSizes, BorderRadius, GlobalStyles } from '../config/theme';

const HomeScreen = ({ navigation }) => {
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [categories, setCategories] = useState([]);
  const [trendingProducts, setTrendingProducts] = useState([]);
  const [flashSales, setFlashSales] = useState([]);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setLoading(true);
      const [categoriesData, productsData] = await Promise.all([
        ApiService.getCategories(),
        ApiService.getProducts({ per_page: 10, sort: 'popular' }),
      ]);

      setCategories(categoriesData || []);
      setTrendingProducts(productsData?.data || []);
    } catch (error) {
      Toast.show({
        type: 'error',
        text1: 'Erreur',
        text2: 'Impossible de charger les données',
      });
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadData();
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
        text2: 'Impossible d\'ajouter au panier',
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
        text2: 'Impossible d\'ajouter aux favoris',
      });
    }
  };

  const renderHeader = () => (
    <LinearGradient
      colors={[Colors.primary, Colors.secondary]}
      style={styles.header}
    >
      <View style={styles.headerTop}>
        <View>
          <Text style={styles.greeting}>Bienvenue sur</Text>
          <Text style={styles.appName}>ICHRI Tunisia</Text>
        </View>
        <TouchableOpacity onPress={() => navigation.navigate('Cart')}>
          <Icon name="shopping-cart" size={28} color={Colors.white} />
        </TouchableOpacity>
      </View>

      {/* Search Bar */}
      <TouchableOpacity
        style={styles.searchBar}
        onPress={() => navigation.navigate('Products')}
      >
        <Icon name="search" size={24} color={Colors.gray} />
        <Text style={styles.searchPlaceholder}>Rechercher des produits...</Text>
      </TouchableOpacity>
    </LinearGradient>
  );

  const renderBanner = () => (
    <View style={styles.bannerContainer}>
      <Swiper
        height={180}
        autoplay
        autoplayTimeout={5}
        showsPagination
        dotColor={Colors.lightGray}
        activeDotColor={Colors.primary}
      >
        <View style={[styles.slide, { backgroundColor: '#FF6B9D' }]}>
          <Text style={styles.slideTitle}>Soldes d'été</Text>
          <Text style={styles.slideSubtitle}>Jusqu'à -50%</Text>
        </View>
        <View style={[styles.slide, { backgroundColor: '#667eea' }]}>
          <Text style={styles.slideTitle}>Nouveautés</Text>
          <Text style={styles.slideSubtitle}>Découvrez nos produits</Text>
        </View>
        <View style={[styles.slide, { backgroundColor: '#4CAF50' }]}>
          <Text style={styles.slideTitle}>Livraison gratuite</Text>
          <Text style={styles.slideSubtitle}>Sur commandes +150 TND</Text>
        </View>
      </Swiper>
    </View>
  );

  const renderCategories = () => (
    <View style={styles.section}>
      <View style={styles.sectionHeader}>
        <Text style={styles.sectionTitle}>Catégories</Text>
        <TouchableOpacity onPress={() => navigation.navigate('Products')}>
          <Text style={styles.seeAll}>Voir tout</Text>
        </TouchableOpacity>
      </View>

      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.categoriesList}
      >
        {categories.map((category) => (
          <TouchableOpacity
            key={category.id}
            style={styles.categoryCard}
            onPress={() => navigation.navigate('Products', { categoryId: category.id })}
          >
            <Icon name="category" size={32} color={Colors.primary} />
            <Text style={styles.categoryName}>{category.name}</Text>
          </TouchableOpacity>
        ))}
      </ScrollView>
    </View>
  );

  const renderTrendingProducts = () => (
    <View style={styles.section}>
      <View style={styles.sectionHeader}>
        <Text style={styles.sectionTitle}>Produits tendance</Text>
        <TouchableOpacity onPress={() => navigation.navigate('Products')}>
          <Text style={styles.seeAll}>Voir tout</Text>
        </TouchableOpacity>
      </View>

      <View style={styles.productsGrid}>
        {trendingProducts.slice(0, 6).map((product) => (
          <ProductCard
            key={product.id}
            product={product}
            onPress={(p) => navigation.navigate('ProductDetail', { productId: p.id })}
            onAddToCart={handleAddToCart}
            onAddToWishlist={handleAddToWishlist}
          />
        ))}
      </View>
    </View>
  );

  if (loading) {
    return (
      <View style={[GlobalStyles.container, GlobalStyles.center]}>
        <Text>Chargement...</Text>
      </View>
    );
  }

  return (
    <View style={GlobalStyles.container}>
      {renderHeader()}
      <ScrollView
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} />
        }
      >
        {renderBanner()}
        {renderCategories()}
        {renderTrendingProducts()}
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  header: {
    padding: Spacing.md,
    paddingTop: 40,
  },
  headerTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: Spacing.md,
  },
  greeting: {
    color: Colors.white,
    fontSize: FontSizes.md,
  },
  appName: {
    color: Colors.white,
    fontSize: FontSizes.xxl,
    fontWeight: 'bold',
  },
  searchBar: {
    backgroundColor: Colors.white,
    borderRadius: BorderRadius.lg,
    padding: Spacing.md,
    flexDirection: 'row',
    alignItems: 'center',
  },
  searchPlaceholder: {
    marginLeft: Spacing.sm,
    color: Colors.gray,
    fontSize: FontSizes.md,
  },
  bannerContainer: {
    height: 180,
    marginTop: Spacing.md,
  },
  slide: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    borderRadius: BorderRadius.lg,
    marginHorizontal: Spacing.md,
  },
  slideTitle: {
    color: Colors.white,
    fontSize: FontSizes.xxl,
    fontWeight: 'bold',
  },
  slideSubtitle: {
    color: Colors.white,
    fontSize: FontSizes.lg,
    marginTop: Spacing.xs,
  },
  section: {
    padding: Spacing.md,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: Spacing.md,
  },
  sectionTitle: {
    fontSize: FontSizes.xl,
    fontWeight: 'bold',
    color: Colors.textPrimary,
  },
  seeAll: {
    color: Colors.primary,
    fontSize: FontSizes.md,
    fontWeight: '600',
  },
  categoriesList: {
    gap: Spacing.md,
  },
  categoryCard: {
    backgroundColor: Colors.white,
    borderRadius: BorderRadius.lg,
    padding: Spacing.lg,
    alignItems: 'center',
    width: 100,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  categoryName: {
    marginTop: Spacing.xs,
    fontSize: FontSizes.sm,
    color: Colors.textPrimary,
    textAlign: 'center',
  },
  productsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
});

export default HomeScreen;
