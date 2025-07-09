# 🏷️ Logo para QR - Instrucciones

## 📁 Ubicación del archivo

El logo debe estar en: `admin/img/logo.png`

## 🎨 Especificaciones recomendadas

### Tamaño

- **Recomendado:** 200x200 píxeles
- **Mínimo:** 100x100 píxeles
- **Máximo:** 500x500 píxeles

### Formato

- **Formato:** PNG (con transparencia)
- **Fondo:** Transparente (recomendado)
- **Color:** Preferiblemente oscuro para contrastar con el QR

### Diseño

- **Forma:** Cuadrada o circular
- **Estilo:** Simple y reconocible
- **Detalles:** Evitar elementos muy pequeños

## 🔧 Cómo funciona

1. **En el editor:** El logo se muestra centrado sobre el QR
2. **Tamaño automático:** Se escala al 20% del tamaño del QR
3. **Posición:** Siempre centrada, sin importar el tamaño del QR
4. **Aplicación:** Se aplica a todos los certificados del curso

## 📝 Ejemplo de uso

```html
<!-- El logo se posiciona automáticamente así: -->
<div class="qr-wrapper" style="width: 300px; height: 300px;">
  <!-- QR SVG aquí -->
  <img src="img/logo.png" class="qr-logo" style="width: 60px;" />
</div>
```

## ⚠️ Notas importantes

- El logo debe existir en la ruta especificada
- Si no existe, el QR se genera sin logo
- El logo se escala proporcionalmente
- Se recomienda usar PNG con transparencia para mejor resultado

## 🎯 Consejos de diseño

1. **Contraste:** Usa colores que contrasten con el QR
2. **Simplicidad:** Evita logos muy complejos
3. **Reconocibilidad:** Que sea fácil de identificar en tamaño pequeño
4. **Marca:** Idealmente usa el logo de tu institución/empresa
