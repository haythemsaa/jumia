import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, ScrollView, StyleSheet } from 'react-native';
import Toast from 'react-native-toast-message';
import ApiService from '../services/api.service';
import { Colors, Spacing, FontSizes, BorderRadius } from '../config/theme';

const CheckoutScreen = ({ navigation }) => {
  const [formData, setFormData] = useState({ address: '', phone: '', notes: '' });

  const handleCheckout = async () => {
    try {
      const order = await ApiService.createOrder({ ...formData, payment_method: 'cash' });
      Toast.show({ type: 'success', text1: 'Commande créée' });
      navigation.navigate('Main');
    } catch (error) {
      Toast.show({ type: 'error', text1: 'Erreur de commande' });
    }
  };

  return (
    <ScrollView style={styles.container}>
      <Text style={styles.title}>Finaliser la commande</Text>
      <TextInput style={styles.input} placeholder="Adresse de livraison" value={formData.address} onChangeText={(val) => setFormData({...formData, address: val})} multiline />
      <TextInput style={styles.input} placeholder="Téléphone" value={formData.phone} onChangeText={(val) => setFormData({...formData, phone: val})} keyboardType="phone-pad" />
      <TextInput style={styles.input} placeholder="Notes (optionnel)" value={formData.notes} onChangeText={(val) => setFormData({...formData, notes: val})} multiline />
      <TouchableOpacity style={styles.button} onPress={handleCheckout}>
        <Text style={styles.buttonText}>Confirmer la commande</Text>
      </TouchableOpacity>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, padding: Spacing.lg, backgroundColor: Colors.white },
  title: { fontSize: FontSizes.xxl, fontWeight: 'bold', marginBottom: Spacing.lg, color: Colors.primary },
  input: { borderWidth: 1, borderColor: Colors.border, borderRadius: BorderRadius.md, padding: Spacing.md, marginBottom: Spacing.md, fontSize: FontSizes.md },
  button: { backgroundColor: Colors.primary, padding: Spacing.lg, borderRadius: BorderRadius.lg, alignItems: 'center', marginTop: Spacing.md },
  buttonText: { color: Colors.white, fontSize: FontSizes.lg, fontWeight: 'bold' },
});

export default CheckoutScreen;
