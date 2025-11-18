import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import Toast from 'react-native-toast-message';
import ApiService from '../services/api.service';
import { STORAGE_KEYS } from '../config/api';
import { Colors, Spacing, FontSizes, BorderRadius } from '../config/theme';

const LoginScreen = ({ navigation }) => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    if (!email || !password) {
      Toast.show({ type: 'error', text1: 'Erreur', text2: 'Veuillez remplir tous les champs' });
      return;
    }

    try {
      setLoading(true);
      const response = await ApiService.login(email, password);
      await AsyncStorage.setItem(STORAGE_KEYS.AUTH_TOKEN, response.token);
      await AsyncStorage.setItem(STORAGE_KEYS.USER_DATA, JSON.stringify(response.user));
      Toast.show({ type: 'success', text1: 'Connexion réussie' });
      navigation.navigate('Main');
    } catch (error) {
      Toast.show({ type: 'error', text1: 'Erreur', text2: 'Email ou mot de passe incorrect' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Connexion</Text>
      <TextInput
        style={styles.input}
        placeholder="Email"
        value={email}
        onChangeText={setEmail}
        keyboardType="email-address"
        autoCapitalize="none"
      />
      <TextInput
        style={styles.input}
        placeholder="Mot de passe"
        value={password}
        onChangeText={setPassword}
        secureTextEntry
      />
      <TouchableOpacity style={styles.button} onPress={handleLogin} disabled={loading}>
        <Text style={styles.buttonText}>{loading ? 'Connexion...' : 'Se connecter'}</Text>
      </TouchableOpacity>
      <TouchableOpacity onPress={() => navigation.navigate('Register')}>
        <Text style={styles.linkText}>Pas de compte ? S'inscrire</Text>
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, padding: Spacing.xl, justifyContent: 'center', backgroundColor: Colors.white },
  title: { fontSize: FontSizes.xxxl, fontWeight: 'bold', marginBottom: Spacing.xl, textAlign: 'center', color: Colors.primary },
  input: { borderWidth: 1, borderColor: Colors.border, borderRadius: BorderRadius.md, padding: Spacing.md, marginBottom: Spacing.md, fontSize: FontSizes.md },
  button: { backgroundColor: Colors.primary, padding: Spacing.md, borderRadius: BorderRadius.lg, alignItems: 'center', marginVertical: Spacing.md },
  buttonText: { color: Colors.white, fontSize: FontSizes.lg, fontWeight: 'bold' },
  linkText: { color: Colors.primary, textAlign: 'center', marginTop: Spacing.md },
});

export default LoginScreen;
