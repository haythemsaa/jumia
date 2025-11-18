/**
 * ICHRI Tunisia Mobile - App Navigator
 */

import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createStackNavigator } from '@react-navigation/stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import Icon from 'react-native-vector-icons/MaterialIcons';
import { Colors } from '../config/theme';

// Screens
import HomeScreen from '../screens/HomeScreen';
import ProductsScreen from '../screens/ProductsScreen';
import ProductDetailScreen from '../screens/ProductDetailScreen';
import CartScreen from '../screens/CartScreen';
import CheckoutScreen from '../screens/CheckoutScreen';
import LoginScreen from '../screens/LoginScreen';
import RegisterScreen from '../screens/RegisterScreen';
import ProfileScreen from '../screens/ProfileScreen';
import OrdersScreen from '../screens/OrdersScreen';
import WishlistScreen from '../screens/WishlistScreen';

const Stack = createStackNavigator();
const Tab = createBottomTabNavigator();

const TabNavigator = () => (
  <Tab.Navigator
    screenOptions={({ route }) => ({
      tabBarIcon: ({ focused, color, size }) => {
        let iconName;

        if (route.name === 'Home') {
          iconName = 'home';
        } else if (route.name === 'Products') {
          iconName = 'search';
        } else if (route.name === 'Cart') {
          iconName = 'shopping-cart';
        } else if (route.name === 'Profile') {
          iconName = 'person';
        }

        return <Icon name={iconName} size={size} color={color} />;
      },
      tabBarActiveTintColor: Colors.primary,
      tabBarInactiveTintColor: Colors.gray,
      headerShown: false,
    })}
  >
    <Tab.Screen name="Home" component={HomeScreen} options={{ title: 'Accueil' }} />
    <Tab.Screen name="Products" component={ProductsScreen} options={{ title: 'Produits' }} />
    <Tab.Screen name="Cart" component={CartScreen} options={{ title: 'Panier' }} />
    <Tab.Screen name="Profile" component={ProfileScreen} options={{ title: 'Profil' }} />
  </Tab.Navigator>
);

const AppNavigator = () => {
  return (
    <NavigationContainer>
      <Stack.Navigator
        screenOptions={{
          headerStyle: {
            backgroundColor: Colors.primary,
          },
          headerTintColor: Colors.white,
          headerTitleStyle: {
            fontWeight: 'bold',
          },
        }}
      >
        <Stack.Screen
          name="Main"
          component={TabNavigator}
          options={{ headerShown: false }}
        />
        <Stack.Screen
          name="ProductDetail"
          component={ProductDetailScreen}
          options={{ title: 'Détail produit' }}
        />
        <Stack.Screen
          name="Checkout"
          component={CheckoutScreen}
          options={{ title: 'Commande' }}
        />
        <Stack.Screen
          name="Login"
          component={LoginScreen}
          options={{ title: 'Connexion' }}
        />
        <Stack.Screen
          name="Register"
          component={RegisterScreen}
          options={{ title: 'Inscription' }}
        />
        <Stack.Screen
          name="Orders"
          component={OrdersScreen}
          options={{ title: 'Mes commandes' }}
        />
        <Stack.Screen
          name="Wishlist"
          component={WishlistScreen}
          options={{ title: 'Mes favoris' }}
        />
      </Stack.Navigator>
    </NavigationContainer>
  );
};

export default AppNavigator;
