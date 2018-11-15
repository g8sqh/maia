import os
import PIL
import numpy as np
from sklearn.feature_extraction.image import extract_patches_2d
from skimage.filters.rank import entropy
from skimage.morphology import disk

class Image(object):

    def __init__(self, id, path):
        self.id = id
        self.path = path

    def random_patch(self, size, vectorize=True):
        return self.random_patches(1, size=size, vectorize=vectorize)[0]

    def random_patches(self, number, size, vectorize=True):
        patches = extract_patches_2d(self.image(), (size, size), max_patches=number)

        return np.reshape(patches, (number, size * size * 3)) if vectorize else patches

    def image(self):
        return np.array(PIL.Image.open(self.path))

    def _get_resized_image(self):
        img = PIL.Image.open(self.path)
        if img.width > img.height:
            width = 500
            height = img.height * width // img.width
        else:
            height = 500
            width = img.width * height // img.height

        return img.resize((width, height), PIL.Image.BILINEAR)

    def extract_pca_features(self):
        return np.array(self._get_resized_image()).flatten()

    def extract_features(self):
        resized_image = np.array(self._get_resized_image().convert('L'))
        e = np.sum(entropy(resized_image, disk(3)))

        return [e]
