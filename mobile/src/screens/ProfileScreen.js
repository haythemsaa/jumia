import React, { useState, useEffect } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, ActivityIndicator } from 'react-native';
import Icon from 'react-native-vector-icons/MaterialIcons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import ApiService from '../services/api.service';
import { STORAGE_KEYS } from '../config/api';
import { Colors, Spacing, FontSizes, BorderRadius, GlobalStyles } from '../config/theme';

const ProfileScreen = ({ navigation }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => { loadProfile(); }, []);

  const loadProfile = async () => {
    try {
      const profile = await ApiService.getProfile();
      setUser(profile);
    } catch (error) {
      navigation.navigate('Login');
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = async () => {
    await AsyncStorage.multiRemove([STORAGE_KEYS.AUTH_TOKEN, STORAGE_KEYS.USER_DATA]);
    navigation.navigate('Login');
  };

  if (loading) return <View style={[GlobalStyles.container, GlobalStyles.center]}><ActivityIndicator size="large" color={Colors.primary} /></View>;

  return (
    <View style={GlobalStyles.container}>
      <View style={styles.header}>
        <View style={styles.avatar}><Icon name="person" size={60} color={Colors.white} /></View>
        <Text style={styles.name}>{user?.first_name} {user?.last_name}</Text>
        <Text style={styles.email}>{user?.email}</Text>
      </View>
      <View style={styles.menu}>
        <TouchableOpacity style={styles.menuItem} onPress={() => navigation.navigate('Orders')}>
          <Icon name="shopping-bag" size={24} color={Colors.primary} />
          <Text style={styles.menuText}>Mes commandes</Text>
          <Icon name="chevron-right" size={24} color={Colors.gray} />
        </TouchableOpacity>
        <TouchableOpacity style={styles.menuItem} onPress={() => navigation.navigate('Wishlist')}>
          <Icon name="favorite" size={24} color={Colors.error} />
          <Text style={styles.menuText}>Mes favoris</Text>
          <Icon name="chevron-right" size={24} color={Colors.gray} />
        </TouchableOpacity>
        <TouchableOpacity style={styles.menuItem} onPress={handleLogout}>
          <Icon name="logout" size={24} color={Colors.error} />
          <Text style={[styles.menuText, { color: Colors.error }]}>Déconnexion</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  header: { backgroundColor: Colors.primary, padding: Spacing.xl, alignItems: 'center', paddingTop: 60 },
  avatar: { width: 100, height: 100, borderRadius: 50, backgroundColor: Colors.secondary, justifyContent: 'center', alignItems: 'center', marginBottom: Spacing.md },
  name: { fontSize: FontSizes.xxl, fontWeight: 'bold', color: Colors.white },
  email: { fontSize: FontSizes.md, color: Colors.white, opacity: 0.8 },
  menu: { padding: Spacing.md },
  menuItem: { backgroundColor: Colors.white, borderRadius: BorderRadius.lg, padding: Spacing.lg, marginBottom: Spacing.md, flexDirection: 'row', alignItems: 'center', shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 4, elevation: 2 },
  menuText: { flex: 1, marginLeft: Spacing.md, fontSize: FontSizes.lg, color: Colors.textPrimary, fontWeight: '600' },
});

export default ProfileScreen;
