import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, ScrollView, StyleSheet } from 'react-native';
import Toast from 'react-native-toast-message';
import ApiService from '../services/api.service';
import { Colors, Spacing, FontSizes, BorderRadius } from '../config/theme';

const RegisterScreen = ({ navigation }) => {
  const [formData, setFormData] = useState({
    first_name: '', last_name: '', email: '', phone: '', password: '', password_confirmation: ''
  });

  const handleRegister = async () => {
    if (!formData.first_name || !formData.email || !formData.password) {
      Toast.show({ type: 'error', text1: 'Veuillez remplir tous les champs' });
      return;
    }
    try {
      await ApiService.register(formData);
      Toast.show({ type: 'success', text1: 'Inscription réussie' });
      navigation.navigate('Login');
    } catch (error) {
      Toast.show({ type: 'error', text1: 'Erreur d\'inscription' });
    }
  };

  return (
    <ScrollView style={styles.container}>
      <Text style={styles.title}>Inscription</Text>
      <TextInput style={styles.input} placeholder="Prénom" value={formData.first_name} onChangeText={(val) => setFormData({...formData, first_name: val})} />
      <TextInput style={styles.input} placeholder="Nom" value={formData.last_name} onChangeText={(val) => setFormData({...formData, last_name: val})} />
      <TextInput style={styles.input} placeholder="Email" value={formData.email} onChangeText={(val) => setFormData({...formData, email: val})} keyboardType="email-address" />
      <TextInput style={styles.input} placeholder="Téléphone" value={formData.phone} onChangeText={(val) => setFormData({...formData, phone: val})} keyboardType="phone-pad" />
      <TextInput style={styles.input} placeholder="Mot de passe" value={formData.password} onChangeText={(val) => setFormData({...formData, password: val})} secureTextEntry />
      <TextInput style={styles.input} placeholder="Confirmer mot de passe" value={formData.password_confirmation} onChangeText={(val) => setFormData({...formData, password_confirmation: val})} secureTextEntry />
      <TouchableOpacity style={styles.button} onPress={handleRegister}>
        <Text style={styles.buttonText}>S'inscrire</Text>
      </TouchableOpacity>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, padding: Spacing.xl, backgroundColor: Colors.white },
  title: { fontSize: FontSizes.xxxl, fontWeight: 'bold', marginBottom: Spacing.xl, textAlign: 'center', color: Colors.primary },
  input: { borderWidth: 1, borderColor: Colors.border, borderRadius: BorderRadius.md, padding: Spacing.md, marginBottom: Spacing.md, fontSize: FontSizes.md },
  button: { backgroundColor: Colors.primary, padding: Spacing.md, borderRadius: BorderRadius.lg, alignItems: 'center', marginVertical: Spacing.md },
  buttonText: { color: Colors.white, fontSize: FontSizes.lg, fontWeight: 'bold' },
});

export default RegisterScreen;
